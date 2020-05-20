# SETTING UP

## Requirements
- docker
- docker-compose
- python 2.7

## Instructions
1. clone the repository
2. execute ```bin/rc install```
3. access the docs on [http://r2-d2.localhost/api/doc](http://r2-d2.localhost/api/doc)

# Using the R2D2 CLI
To make things easier in local environments, we have the r2d2 cli, that wraps up some commands we constantly need to run.

## How to run it
```bin/rc COMMAND EXTRA_PARAMETERS```

## Available commands  
- install
    - Runs build, start, composer install and migrations
- start
    - starts the stack
- stop
    - stops the stack
- composer
    - route commands directly to the composer binary in the container
- console
    - route commands directly to the symfony binary in the container
- build
    - builds the images for using in local environment (currently only the php image)
- phpunit
    - runs phpunit against the codebase
- phpstan
    - runs phpstan with level 8
- destroy
    - removes all containers, networks and volumes
