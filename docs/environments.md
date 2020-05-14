# Environments


## Project env

| Node | Service |
| --- | --- |
| ie1-devint-r2d2-api-01.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` |
| ie1-devint-r2d2-db-01.z.sbxtest.net | `R2-D2 MySQL 8.0` |
| 10.8.116.13 | `EAI RabbitMQ` |

| Service | Access | User | Password | Roles |
| --- | --- | --- | --- | --- |
| R2D2 | https://r2-d2-api-devint.sbxtest.net/ | admin | admin | everything, `/internal` |
| R2D2 | https://r2-d2-api-devint.sbxtest.net/ | eai | eai | `/broadcast-listener`  |
| R2D2 | https://r2-d2-api-devint.sbxtest.net/ | booking | booking | `/booking` |
| R2D2 | https://r2-d2-api-devint.sbxtest.net/ | | | `/quickdata`, `/cmhub`, `/`, `/ping` |
| RabbitMQ | http://10.8.116.13:15672/ | r2d2 | r2d2 | |
| MySQL | ie1-devint-r2d2-db-01.z.sbxtest.net:3306 | u_r2d2 | JxUfkzcBigPE^Z*4 | |

## Preprod

| Node | Service |
| --- | --- |
| r2-d2-api-preprod.sbxtest.net | `LoadBalancer` |
| ie1-pp-r2d2-api-01.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` |
| ie1-pp-r2d2-api-02.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` |
| ie1-pp-r2d2-db-01.z.sbxtest.net | `R2-D2 MySQL 8.0 Master` |
| ie1-pp-r2d2-db-02.z.sbxtest.net | `R2-D2 MySQL 8.0 Slave` |
| ie1-pp-r2d2-rabbitmq-01.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-02.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-03.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-04.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pp-r2d2-rabbitmq-05.z.sbxtest.net | `R2-D2 RabbitMQ` |

| Service | Access | User | Password | Roles |
| --- | --- | --- | --- | --- |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | admin | admin | everything, `/internal` |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | eai | eai | `/broadcast-listener`  |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | booking | booking | `/booking` |
| R2D2 | https://r2-d2-api-preprod.sbxtest.net/ | | | `/quickdata`, `/cmhub`, `/`, `/ping` |
| RabbitMQ | http://ie1-pp-r2d2-rabbitmq-01.z.sbxtest.net:15672/ | rabbit-admin | 3iwgXykuxdO | |
| MySQL | ie1-pp-r2d2-db-01.z.sbxtest.net:3306 | ??? | ???? | |


## PRODUCTION

| Node | Service |
| --- | --- |
| r2-d2-api.production.smartbox.com | `LoadBalancer` |
| ie1-pr-r2d2-api-01.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` |
| ie1-pr-r2d2-api-02.z.sbxtest.net | `R2-D2 API` `R2-D2 Worker` |
| ie1-pr-r2d2-db-01.z.sbxtest.net | `R2-D2 MySQL 8.0 Master` |
| ie1-pr-r2d2-db-02.z.sbxtest.net | `R2-D2 MySQL 8.0 Slave` |
| ie1-pr-r2d2-rabbitmq-01.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-02.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-03.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-04.z.sbxtest.net | `R2-D2 RabbitMQ` |
| ie1-pr-r2d2-rabbitmq-05.z.sbxtest.net | `R2-D2 RabbitMQ` |

| Service | Access | User | Password | Roles |
| --- | --- | --- | --- | --- |
| R2D2 | https://r2-d2-api.production.smartbox.com/ | - | - | `/quickdata`, `/cmhub`, `/`, `/ping` |
| RabbitMQ | http://ie1-pr-r2d2-rabbitmq-01.z.sbxtest.net:15672/ | - | - | |
| MySQL | ie1-pr-r2d2-db-01.z.sbxtest.net:3306 | - | - | |
