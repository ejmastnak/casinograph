# NAME
#     casinograph.bash - Draw a node-link graph from positions and figures
#
# SYNOPSIS
#     casinograph.bash db.sqlite path/to/output.svg base_url
#
# PARAMETERS
#     $1 path to app's database
#     $2 path at which to save graph
#     $3 base url for links
#
# DESCRIPTION
#    Uses Graphviz to draw a node-link representation of the graph formed by
#    casino figures and positions, where positions are vertices and figures are
#    edges.
#
#    Graphviz (DOT) file looks something like this:

#    >    digraph casinograph {
#    >      1 [label="Open"];
#    >      2 [label="Caida"];
#    >
#    >      1 -> 2 [label=" Enchufa"];
#    >    }

if [[ "$#" -ne 3 ]]; then
  >&2 echo "Error: illegal number of parameters. Usage: casinograph.bash db.sqlite path/to/output.svg base_url"
  exit 1
fi

# From https://stackoverflow.com/a/677212
if ! command -v dot &> /dev/null; then
  >&2 echo "Error: dot is not executable."
  exit 1
fi

DB="${1}"
OUTPUT="${2}"
BASE_URL="${3}"
POSITIONS="${BASE_URL}/positions"
FIGURES="${BASE_URL}/figures"

if [[ ! -e ${DB} ]]; then
    >&2 echo "Error: database ${DB} does not exist."
    exit 1
elif [[ ! -w ${OUTPUT%/*} ]]; then
    >&2 echo "Error: output directory ${OUTPUT%/*} is not writable."
    exit 1
fi

# Temporary DOT file with instructions for Graphviz
TMP_GV="casinograph-`date +%Y-%m-%dT%H:%M:%S`.gv"
function cleanup {
    rm -f ${TMP_GV}
}
trap cleanup EXIT INT ABRT KILL TERM ERR

# Positions query is meant to leave out orphaned positions
POSITIONS_QUERY="select positions.id,positions.name from positions inner join figures on figures.from_position_id = positions.id or figures.to_position_id = positions.id group by positions.id;"
FIGURES_QUERY="select id,from_position_id, to_position_id, name from figures;"

# --------------------------------------------------------------------------- #
# Prepare DOT file for Graphviz
# --------------------------------------------------------------------------- #
echo 'digraph CasinoGraph {' > ${TMP_GV}

# Notes to self for later:
# label=\"%s\" becomes e.g. label="Enchufa"
# URL=\"%s/%s\" becomes e.g. URL="https://casinograph.ejmastnak.com/positions/1"

# Global options
echo '  node [fontname="Figtree", fontcolor="#172554", color="#172554", style=filled, fillcolor="#eff6ff", target="_top"];' >> ${TMP_GV}
echo '  edge [fontname="Figtree", fontcolor="#172554", color="#172554", target="_top"];' >> ${TMP_GV}

# Positions
sqlite3 ${DB} "${POSITIONS_QUERY}" \
  | awk -F '|' \
  -v URL="${POSITIONS}" \
  '{printf "  %s [label=\"%s\", URL=\"%s/%s\"];\n",$1,$2,URL,$1}' \
  >> ${TMP_GV}
printf "\n" >> ${TMP_GV}

# Figures
sqlite3 ${DB} "${FIGURES_QUERY}" \
  | awk -F '|' \
  -v URL="${FIGURES}" \
  '{printf "  %s -> %s [label=\" %s \", URL=\"%s/%s\"];\n",$2,$3,$4,URL,$1}' \
  >> ${TMP_GV}
echo "}" >> ${TMP_GV}
# --------------------------------------------------------------------------- #

# Draw graph
dot -Tsvg ${TMP_GV} -o "${OUTPUT}"
