# Docker Swarm

We decided to use Docker Swarm to make our deploys easier, and also to easily scale the number of the specialised workers.



## Setting up the Swarm

##### Manager or worker?

In Docker Swarm, we have two types of nodes: _manager_ and _worker_.
 
Manager nodes can modify the status of the swarm, and worker nodes can't.

For R2D2, we'll use the __node 01__ as a manager. Eventually, when we increase the number of nodes we have,
we may need to add more managers, but _we must always keep it an odd number_.


##### Setting up the manager
On the manager node (any node), you need to run
```shell script
$ docker swarm init
```

This command will output the following:
```
Swarm initialized: current node (tuewqewsl9wcm40dgdrdi11a2) is now a manager.

To add a worker to this swarm, run the following command:

    docker swarm join --token SWMTKN-1-1wng5xiso8emqtdxbjhtd6gz9lqg9wprqm6js5ohczhc6mjxc6-7zy88eytobd61gohrkk6sxye1 10.29.21.62:2377

To add a manager to this swarm, run 'docker swarm join-token manager' and follow the instructions.
```

This command outputted is what you'll need to run in the other nodes to add it to the swarm. Go to the other nodes and run it.

##### Setting up the workers
```
$ docker swarm join --token SWMTKN-1-1wng5xiso8emqtdxbjhtd6gz9lqg9wprqm6js5ohczhc6mjxc6-7zy88eytobd61gohrkk6sxye1 10.29.21.62:2377
This node joined a swarm as a worker.
```

Do this to all nodes in the environment, and the swarm is ready to be used!

## Managing replicas

By default, swarm starts only a single container for each service listed in docker-compose.yaml.
To set up the number of replicas to be spun up, you need to set it in the docker-compose:

```yaml
version: '3.4'

services:
  api:
    image: any_image:any-version
    deploy:
      replicas: 2
```
The replicas will be spun up across all swarm nodes.

For API (public-facing) containers, you need to spin up a number of replicas equal to the numbers of node you have.

## Scaling the number of workers

This needs to be run in a manager node:

```shell script
$ docker service scale r2d2_worker-broadcast-listeners-price-information=4
``` 
``` 
r2d2_worker-broadcast-listeners-price-information scaled to 4
overall progress: 4 out of 4 tasks 
1/4: running   [==================================================>] 
2/4: running   [==================================================>] 
3/4: running   [==================================================>] 
4/4: running   [==================================================>] 
verify: Service converged 
```

Replicas will be spread across the swarm cluster.

## Visualising the swarm services

You can see all the services deployed in a swarm, with the amount of live replicas by issuing the following command:

```shell script
$ docker service ls
```
```
ID                  NAME                                                   MODE                REPLICAS            IMAGE                                                                                                               PORTS
15fzqs2actyr        r2d2_api                                               replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
z5vxno90vnt8        r2d2_worker                                            replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
g2cfq9z455kp        r2d2_worker-broadcast-listeners-partner                replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
zrwyudrb308x        r2d2_worker-broadcast-listeners-price-information      replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
j2t3vp4u62o8        r2d2_worker-broadcast-listeners-product                replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
8bcy7igwca3d        r2d2_worker-broadcast-listeners-product-relationship   replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
u2i15euhlgqc        r2d2_worker-calculate-manageable-flag                  replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   
n3pu2quwjv17        r2d2_worker-push-room-information                      replicated          2/2                 docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d
```

Also, you can see exactly where each service is with the following command:

```shell script
$ docker stack ps r2d2
```
```
ID                  NAME                                                     IMAGE                                                                                                               NODE                               DESIRED STATE       CURRENT STATE           ERROR               PORTS
xmq5sg44nnfg        r2d2_worker-broadcast-listeners-price-information.1      docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
lqwsfy0g24xu        r2d2_worker-broadcast-listeners-product.1                docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
y00phljvchyf        r2d2_worker-broadcast-listeners-partner.1                docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 2 minutes ago                       
zgkiw64ile0n        r2d2_worker.1                                            docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
qbtsjwhd940g        r2d2_api.1                                               docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       *:80->80/tcp
oszq41fypkfa        r2d2_worker-push-room-information.1                      docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 3 minutes ago                       
l9z8gst2xb55        r2d2_worker-calculate-manageable-flag.1                  docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
v86h3poo3b9k        r2d2_worker-broadcast-listeners-product-relationship.1   docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
c1um279c95vz        r2d2_worker-broadcast-listeners-price-information.2      docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 2 minutes ago                       
m7mnx422ohtn        r2d2_worker-broadcast-listeners-product.2                docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 2 minutes ago                       
ksw09t0nscol        r2d2_worker-broadcast-listeners-partner.2                docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
g6siy2jxhgvo        r2d2_worker.2                                            docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 3 minutes ago                       
5y0h1ky39ky2        r2d2_api.2                                               docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 3 minutes ago                       *:80->80/tcp
b5rkobxwqona        r2d2_worker-push-room-information.2                      docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       
kjntdrfy7lcr        r2d2_worker-calculate-manageable-flag.2                  docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 3 minutes ago                       
z6t1wrwcfsl2        r2d2_worker-broadcast-listeners-product-relationship.2   docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 3 minutes ago                       
```

## Publishing ports

Differently from regular deploys, deploying with swarm means we need to explictly tell it which ports to expose,
and which ways to expose it.

```yaml
version: '3.4'

services:
  api:
    image: any_image:any-version
    deploy:
      replicas: 2
    ports:
      - target: 80
        published: 80
        protocol: tcp
        mode: host
``` 
The key `mode: host` here means that it won't use any swarm feature to route the traffic between nodes. If we were to
use ingress, where we could spin a bigger number of api nodes, we can use `mode: ingress`
```yaml
version: '3.4'

services:
  api:
    image: any_image:any-version
    deploy:
      replicas: 4
    ports:
      - target: 80
        published: 80
        protocol: tcp
        mode: ingress
``` 


## Blue-green deployment

In R2D2 swarm, we enabled blue-green deployment for the api nodes. To do that, you need to tell docker to start
the containers after updating the service definition. That can be done with the following snippet in docker-compose.yaml:
  
```yaml
version: '3.4'

services:
  api:
    image: any_image:any-version
    deploy:
      replicas: 2
      update_config:
        order: start-first
```
Values for `update_config.order` can be `start-first` or `stop-first`.

 * _stop-first:_ traditional deploy - stop the containers and then spin up the new ones
 * _start-first_ blue-green deployment - stop the containers only after spinning up the new ones

## Managing secrets

We're using vault for storing secrets. All keys in vault will be exposed to the containers
as environment variables, replacing the need for the `.env` file.

Please note we won't pull any files from vault, only the keys and values.

## Deploying

Deploy to a swarm uses the docker CLI connecting to a remote host, defined by the DOCKER_HOST environment variable.

```shell script
DOCKER_HOST=ssh://my-production-node.smartbox.com docker stack deploy --compose-file my-docker-compose-file.yaml my-service
```
The variable `DOCKER_HOST` must point to a swarm manager node, and it will flow to the other nodes from there.
There's no need to deploy to any other node. 

## Re-building the swarm

If everything goes wrong, we can always rebuild the entire swarm, and redeploy the application.
For that, please make sure no nodes are present on the swarm, by making each of them leave it:
```shell script
$ docker swarm leave --force
```
Then, setting up following the steps on [Setting up the Swarm](#setting-up-the-swarm).

After it's rebuild, you can run the deploy again.


## Troubleshooting

1. _A node was somehow removed from the swarm. What should I do?_

    You can re-add the node to the swarm by running the swarm join command. To retrieve it, go to the swarm master
    and run the following command:
    ```shell script
    $ docker swarm join-token worker
    ```
   
   It will generate the worker join command that you need to run in your node. Please refer to
   [Setting up the manager](#setting-up-the-manager) and [Setting up the workers](#setting-up-the-workers).
 
2. _Can't access the API docs when connecting directly to the nodes!_

   Check if the API containers are up, and with the port 80 exposed:
   
    ```shell script
    $ docker stack ps r2d2
    ```
   ```
   qbtsjwhd940g    r2d2_api.1  docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-02.z.sbxtest.net   Running             Running 3 minutes ago                       *:80->80/tcp
   5y0h1ky39ky2    r2d2_api.2  docker-registry.production.smartbox.com/millenium-falcon/r2d2-api:172450_5717185cb2f25980ac786744b0bcd534ad6ef80d   ie1-pp-r2d2-api-01.z.sbxtest.net   Running             Running 3 minutes ago                       *:80->80/tcp
   ```
   
   If they are up, the easiest way to fix it would be redeploying it, or, as a last resort, re-building the swarm. 
 
3. _Deploy is not working! Looks like the vault token expired!_

   You can ask someone from Platform Engineering to generate a new vault key for you, and update it
   on GitLab CI Variables (`MOBY_VAULT_TOKEN` for moby token, and `VAULT_APP_TOKEN` for the app secrets token)
   
4. _Looks like vault is down!_

   _Oh, well..._
