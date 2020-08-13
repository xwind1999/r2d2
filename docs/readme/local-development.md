# SETTING UP

## Requirements
- docker
- docker-compose
- D.E.F.U.S.E

## Instructions
1. follow the steps for D.E.F.U.S.E: [http://shared-tools.gitlab.production.smartbox.com/defuse](http://shared-tools.gitlab.production.smartbox.com/defuse)
2. install r2d2 with `sbx install-service r2d2`
3. access the docs on [http://r2d2.localhost](http://r2d2.localhost)

# Using the R2D2 CLI
To make things easier in local environments, we are using the D.E.F.U.S.E tool to wrap up some commands we constantly need to run.

## How to run it
```shell script
sbx r2d2 COMMAND EXTRA_PARAMETERS
```

## Available commands  

Please refer to [http://shared-tools.gitlab.production.smartbox.com/defuse/#/R2D2](http://shared-tools.gitlab.production.smartbox.com/defuse/#/R2D2)

## Create local database dump

As the script should run locally, you need to have a MySQL database installed and configured with the right parameters.

From the r2d2 directory, type the following command in the terminal:

```shell script
sh utils/fixtures/create-dump.sh
```

To create the database dump from another host, just replace the connection variables in the file to the desirable host. 

## Load local database dump

Type the following commands in the terminal:

```shell script
sbx r2d2 console doctrine:database:import utils/fixtures/dumps/*
sbx r2d2 console doctrine:fixtures:load --append
```
    
> Make sure to use the _--append_ at the end of the fixtures:load command. Otherwise, it will purge the database.
 