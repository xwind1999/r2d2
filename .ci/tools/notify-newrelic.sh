#!/bin/sh

if [ "$NR_ENABLE" = "true" ]; then
  ${CI_PROJECT_DIR}/.ci/tools/knife-master.sh $1 "docker exec r2d2_api_1 $2"
else
  echo "----------- NEW RELIC IS NOT ENABLED - SKIPPING -----------"
fi
