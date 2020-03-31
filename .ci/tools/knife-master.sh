#!/bin/sh

knife ssh "role:sm-jarvis-r2d2api AND chef_environment:$1 AND tags:master" "$2" -a ipaddress
