# Environments


## Project env

| Node | Service |
| --- | --- |
| ie1-devint-r2d2-api-01.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` `Portainer` |
| ie1-devint-r2d2-db-01.z.sbxtest.net | `R2-D2 MySQL 8.0` |
| 10.8.116.13 | `EAI RabbitMQ` |

| Service | Access | User | Password | Roles |
| --- | --- | --- | --- | --- |
| Portainer | http://r2-d2-api-devint.sbxtest.net:9000 | admin | tCXfu8fbp87LRX | |
| R2D2 | http://r2-d2-api-devint.sbxtest.net/ | admin | admin | everything, `/internal` |
| R2D2 | http://r2-d2-api-devint.sbxtest.net/ | eai | eai | `/broadcast-listener`  |
| R2D2 | http://r2-d2-api-devint.sbxtest.net/ | booking | booking | `/booking` |
| R2D2 | http://r2-d2-api-devint.sbxtest.net/ | | | `/quickdata`, `/cmhub`, `/`, `/ping` |
| RabbitMQ | http://10.8.116.13:15672/ | rabbit-admin | xgdfykuxdO | |
| MySQL | ie1-devint-r2d2-db-01.z.sbxtest.net:3306 | u_r2d2 | JxUfkzcBigPE^Z*4 | |

## Preprod

| Node | Service |
| --- | --- |
| r2-d2-api-preprod.sbxtest.net | `LoadBalancer` |
| ie1-pp-r2d2-api-01.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` `Portainer` |
| ie1-pp-r2d2-api-02.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` `Portainer` |
| ie1-pp-r2d2-db-01.z.sbxtest.net | `R2-D2 MySQL 8.0 Master` |
| ie1-pp-r2d2-db-02.z.sbxtest.net | `R2-D2 MySQL 8.0 Slave` |
| ie1-pp-r2d2-rabbitmq-01.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-02.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-03.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-04.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-05.z.sbxtest.net | `R2-D2 RabbitMQ` |

| Service | Access | User | Password | Roles |
| --- | --- | --- | --- | --- |
| Portainer | http://ie1-pp-r2d2-api-01.z.sbxtest.net:9000/ | admin | starwarssucks | |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | admin | admin | everything, `/internal` |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | eai | eai | `/broadcast-listener`  |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | booking | booking | `/booking` |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | | | `/quickdata`, `/cmhub`, `/`, `/ping` |
| RabbitMQ | http://ie1-pp-r2d2-rabbitmq-01.z.sbxtest.net:15672/ | rabbit-admin | 3iwgXykuxdO | |
| MySQL | ie1-pp-r2d2-db-01.z.sbxtest.net:3306 | u_r2d2 | gT6%Ne^m6DO8QGwi3j62 | |


## PRODUCTION

| Node | Service |
| --- | --- |
| r2-d2-api.production.smartbox.com | `LoadBalancer` |
| ie1-pr-r2d2-api-01.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` `Portainer` |
| ie1-pr-r2d2-api-02.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` `Portainer` |
| ie1-pr-r2d2-db-01.z.sbxtest.net | `R2-D2 MySQL 8.0 Master` |
| ie1-pr-r2d2-db-02.z.sbxtest.net | `R2-D2 MySQL 8.0 Slave` |
| ie1-pr-r2d2-rabbitmq-01.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-02.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-03.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-04.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-05.z.sbxtest.net | `R2-D2 RabbitMQ` |

| Service | Access | User | Password | Roles |
| --- | --- | --- | --- | --- |
| Portainer | http://ie1-pr-r2d2-api-01.z.sbxtest.net:9000/ | | | |
| R2D2 | https://r2-d2-api.production.smartbox.com/ | - | - | `/quickdata`, `/cmhub`, `/`, `/ping` |
| RabbitMQ | http://ie1-pr-r2d2-rabbitmq-01.z.sbxtest.net:15672/ | - | - | |
| MySQL | ie1-pr-r2d2-db-01.z.sbxtest.net:3306 | - | - | |

For production credentials,
[please click here](https://smartbox.atlassian.net/wiki/spaces/R2D2/pages/1720647893/Production+Credentials).
