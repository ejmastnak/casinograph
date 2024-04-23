#!/bin/bash
# NAME
#     copy-tables.bash - Copy records in database to a new user
# SYNOPSIS
#     copy-table db old_user_id new_user_id
#
#     Example inputs:
#     db="path/to/database.sqlite"
#     old_user_id="1"  # can be "null" or '0' for NULL
#     new_user_id="42"
#
# Used in practice to create a copies of existing records for different users,
# e.g. to copy all records with NULL user_id to user 1

DB=${1}
OLD_USER_ID=${2}
NEW_USER_ID=${3}

declare -a TABLES=(\
  "position_families" \
  "figure_families" \
  "positions" \
  "figures" \
  "compound_figures" \
  "compound_figure_figures" \
)
declare -a COLUMNS=(\
  "name, created_at, updated_at" \
  "name, created_at, updated_at" \
  "name, description, position_family_id, created_at, updated_at" \
  "name, description, weight, figure_family_id, from_position_id, to_position_id, created_at, updated_at" \
  "name, description, weight, figure_family_id, from_position_id, to_position_id, created_at, updated_at" \
  "figure_id, compound_figure_id, idx, is_final, created_at, updated_at" \
)

# FUNCTION
#     copy_table table columns
#
# DESCRIPTION
#     Creates a copy of all records in `table` where `user_id` equals
#     ${OLD_USER_ID}, and sets the `user_id` of the copy to ${new_user_id}.
#
#     Example inputs:
#     table="position_families"
#     columns="name, created_at, updated_at"
#     old_user_id="0"  # set to 0 for null
#     new_user_id="1"
copy_table () {
  table="${1}"
  columns="${2}"

  if [[ ${OLD_USER_ID} == "null" || ${OLD_USER_ID} == "NULL" || ${OLD_USER_ID} -lt 1 ]]; then
    where_user="WHERE user_id IS NULL"
  else
    where_user="WHERE user_id = ${OLD_USER_ID}"
  fi
  query="INSERT INTO ${table} (user_id, ${columns}) SELECT ${NEW_USER_ID}, ${columns} FROM ${table} ${where_user};"

  sqlite3 "${DB}" "${query}"
  # echo "sqlite3 '${DB}' '${query}'"
}

for i in "${!TABLES[@]}"; do
  table="${TABLES[$i]}"
  columns="${COLUMNS[$i]}"
  copy_table "${table}" "${columns}"
done
