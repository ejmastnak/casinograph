# NAME
#     casinograph.bash - Draw a node-link graph from positions and figures
#
# SYNOPSIS
#     casinograph.bash
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

# DB="/home/ej/Documents/projects/casino/app/database/sqlite/database.sqlite"
DB=${1}
IMG="/home/ej/Documents/projects/casino/app/public/img"

GV="casinograph-`date +%Y-%m-%dT%H:%M:%S`.gv"
GRAPH="/home/ej/Documents/projects/casino/app/public/img/casinograph.svg"
BASE_URL="https://casinograph.ejmastnak.com"
POSITIONS="${BASE_URL}/positions"
FIGURES="${BASE_URL}/figures"

# Positions query is meant to leave out orphaned positions
POSITIONS_QUERY="select positions.id,positions.name from positions inner join figures on figures.from_position_id = positions.id or figures.to_position_id = positions.id group by positions.id;"
FIGURES_QUERY="select id,from_position_id, to_position_id, name from figures;"

# Notes to self for later:
# label=\"%s\" becomes e.g. label="Enchufa"
# URL=\"%s/%s\" becomes e.g. URL="https://casinograph.ejmastnak.com/positions/1"

# --------------------------------------------------------------------------- #
# Prepare graph's DOT file
# --------------------------------------------------------------------------- #
echo 'digraph CasinoGraph {' > ${GV}

# Global options
echo '  node [fontname="Figtree", fontcolor="#172554", color="#172554", style=filled, fillcolor="#eff6ff"];' >> ${GV}
echo '  edge [fontname="Figtree", fontcolor="#172554", color="#172554"];' >> ${GV}

# Positions
sqlite3 ${DB} "${POSITIONS_QUERY}" \
  | awk -F '|' \
  -v URL="${POSITIONS}" \
  '{printf "  %s [label=\"%s\", URL=\"%s/%s\"];\n",$1,$2,URL,$1}' \
  >> ${GV}
printf "\n" >> ${GV}

# Figures
sqlite3 ${DB} "${FIGURES_QUERY}" \
  | awk -F '|' \
  -v URL="${FIGURES}" \
  '{printf "  %s -> %s [label=\" %s \", URL=\"%s/%s\"];\n",$2,$3,$4,URL,$1}' \
  >> ${GV}
echo "}" >> ${GV}
# --------------------------------------------------------------------------- #


# --------------------------------------------------------------------------- #
# Draw graph and clean up
# --------------------------------------------------------------------------- #
dot -Tsvg ${GV} -o ${GRAPH}
rm -f ${GV}
# --------------------------------------------------------------------------- #
