#!/bin/bash

FROM="seed.sqlite"
TABLES="./sql"

declare -a tables=("figure_families" "position_families" "positions" "figures" "compound_figures" "compound_figure_figures")

mkdir -p ${TABLES}

for table in "${tables[@]}"
do
  sqlite3 ${FROM} ".dump ${table}" > ${TABLES}/${table}.sql
done
