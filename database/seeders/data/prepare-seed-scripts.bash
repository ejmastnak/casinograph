#!/bin/bash
# Creates SQL dump statements for seeding Laravel DB tables

PROD_DB="prod.sqlite"
DB="seed.sqlite"
TABLES="./sql"
CASINO_USER_ID=1

declare -a tables=("figure_families" "position_families" "positions" "figures" "compound_figures" "compound_figure_figures")

cp -f ${PROD_DB} ${DB}

mkdir -p ${TABLES}

for table in "${tables[@]}"; do
  sqlite3 ${DB} "UPDATE ${table} SET user_id = ${CASINO_USER_ID} WHERE user_id IS NULL;"
  sqlite3 ${DB} ".dump ${table}" > ${TABLES}/${table}.sql
done
