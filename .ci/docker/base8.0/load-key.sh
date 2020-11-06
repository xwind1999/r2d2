#!/bin/sh
mkdir -p ~/.ssh
echo "$1" > ~/.ssh/known_hosts
echo "$2" > ~/.ssh/id_rsa
chmod 700 ~/.ssh -R
