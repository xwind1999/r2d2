#!/bin/bash
(
  DIR=$(readlink -f $(dirname "$0"))
  cd "$DIR" || return

  echo "Dumping database"

  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables box > dumps/00_box.sql
  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables partner > dumps/01_partner.sql
  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables experience > dumps/02_experience.sql
  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables component > dumps/03_component.sql
  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables box_experience > dumps/04_box_experience.sql
  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables experience_component > dumps/05_experience_component.sql
  mysqldump -uroot -padmin123 -h127.0.0.1 --port 3308 --databases r2d2 --no-create-db  --no-create-info --complete-insert --compact --tables flat_manageable_component > dumps/06_flat_manageable_component.sql

  echo "Done!"
)