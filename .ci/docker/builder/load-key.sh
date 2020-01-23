#!/bin/sh
echo "whatever:x:$(id -u):$(id -g):whatever:/tmp:/sbin/nologin" >> /etc/passwd
echo "postfix:x:$(id -g):" >> /etc/group
mkdir -p ~/.ssh
echo "$1" > ~/.ssh/known_hosts
echo "$2" > ~/.ssh/id_rsa
chmod 700 ~/.ssh -R
