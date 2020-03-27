#!/bin/sh

mkdir /service/sym
cp /command-runner /service/sym/run

exec /sbin/runsvdir /service
