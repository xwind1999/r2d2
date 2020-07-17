#!/bin/bash

DIR=$(readlink -f $(dirname "$0"))

cd "$DIR" || return

filename="load-dump.sql"

echo "Export $filename database"

mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --tables box partner experience component box_experience experience_component --no-create-db  --no-create-info --complete-insert --compact >  "$filename"

echo "Output file :"

ls -lh "$filename"

