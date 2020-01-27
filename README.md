###### Develop branch Gitlab Status:
![pipeline status](http://gitlab.production.smartbox.com/millenniumfalcon/r2-d2-api/badges/master/pipeline.svg)
![coverage report](http://gitlab.production.smartbox.com/millenniumfalcon/r2-d2-api/badges/master/coverage.svg)


###### Reports and generated documentation:
* [Unit Tests Coverage Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/phpunit/)
* [PHPMetrics Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/phpmetrics/)

# R2D2 API

> A resourceful astromech droid, R2-D2 served PadméAmidala, Anakin Skywalker and Luke Skywalker in turn, showing great bravery in rescuing his masters and their friends from many perils.

## Main Responsibilities (domains):

* Provides enriched A&P (availability and pricing) data to JarvisBooking, giving JB the ability to fetch A&P by box, experience, partner, box, room and rate band;
* Translates A&P data from Stay Business Data Model (CMHub and CMs, based on rooms and rate bands) to Smartbox Data Model (based on experiences and boxes);
* Provides enriched Booking data provided to CMHub by adding boxes and experiences information to Bookings that might be needed by the CMs;
* Routes bookings calls;
* Receives A&P from CMHub.

## Master data for:

* Enriched A&P

## Stores data for:

* Availability and Upsell Prices
* Product information

## Contextualization

R2-D2 is part of the iResa deprecation plans. Follows bellow some external references to give some context about its creation and specification.

* [2019-07-04 - Learnings and new approach presentation](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1038188835/2019-07-04+-+Learnings+and+new+approach+presentation)
* [2019-07-22 - Workshop 1](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1038090633/2019-07-22+-+Workshop+1)
* [2019-07-30 - Workshop 2](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1037535483/2019-07-30+-+Workshop+2)
* [2019-08-01 - The consolidated proposal presentation](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1042022407/2019-08-01+-+The+consolidated+proposal+presentation)
* [2019-08-19 - Product info from PIM vs Catalog](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1054081813/2019-08-19+-+Product+info+from+PIM+vs+Catalog)
* [2019-08-21 - CMHub and R2D2 interactions](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1054736799/2019-08-21+-+CMHub+and+R2D2+interactions)
* [2019-08-21 - JarvisBooking and R2D2 interactions](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1054900625/2019-08-21+-+JarvisBooking+and+R2D2+interactions)

## Technical information

* [Interactions Overview](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/architecture/diagram/high-level-interactions-overview.png)
* [High level architecture prototype](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/architecture/diagram/high-level-architecture-prototype.png)
* [High level data model](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/schema/high-level-data-model.png)
* [Data Model](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/schema/r2-d2-api-data-model-alpha.png)
* [API Reference](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/reference/r2d2-api/openapi.html)
* [Channel Room Availability flow change](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/flow/broadcast_channel_room_availability.xlsx)
* [Partner Information flow change](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/flow/broadcast_partner_information.xlsx)
* [Product Information flow change](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/flow/broadcast_product_information.xlsx)
* [Product Relationship flow change](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/flow/broadcast_product_relationship.xlsx)

## External resources and extra information

* [Confluence: R2-D2 - The Availability & Pricing Engine](https://smartbox.atlassian.net/wiki/spaces/MF/pages/305464114/R2-D2+The+Availability+Pricing+Engine)
* [Confluence: R2-D2 - Service Introduction Page](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1054212836/R2-D2+-+Service+Introduction+Page)
* [Confluence: R2-D2 - Data Model](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1058701540/Data+Model)
* [Confluence: R2-D2 - API Design](https://smartbox.atlassian.net/wiki/spaces/MF/pages/1058504999/API+Design)
* [Confluence: CP000929 - Introduce R2D2 Availability & Pricing Engine Service](https://smartbox.atlassian.net/wiki/spaces/CHANGEHUB/pages/1081868749/CP000929+-+Introduce+R2D2+Availability+Pricing+Engine+Service)

## High level tasks

- [x] Iterate over the first R2-D2 + C3-PO solution and provide an updated design
- [x] Receive feedback updated technical proposal
- [x] Based on the feedback, evolve the new proposal by conducing workshops with impacted systems
- [x] Present the consolidated proposal impacted systems, stakeholders and IT leadership
- [x] Draft a high level data model
- [x] Draft an initial database model to cover the MVP
- [x] Draft an initial API specification and design to cover the MVP
- [x] Receive initial feedback about the drafted data model and API design
- [x] Draft the new flow documents for Booking, Room Prices and Room Availabilities
- [x] Present the technical solution to the Architecture Board
- [ ] Review data model and API design based on feedback provided by the Arch Board.
  - [x] Use golden_id rather than smartbox_id
  - [ ] Add booking flow and cdm changes
  - [ ] Expose rooms to CMHub
  - [ ] Review impact in R2-D2 of room chaining being moved to PRM
- [ ] Break down the implementation epics
- [ ] Raise a delivery plan taking into account the impacted systems and dependencies
- [ ] Bootstrap a team
- [ ] ...

* [High level plan](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/docs/roadmap/high-level-plan.png)
* [JIRA: R2-D2 - Roadmap WIP](https://smartbox.atlassian.net/secure/Roadmap.jspa?projectKey=R2D2&rapidView=482)
* [JIRA: R2-D2 - Board](https://smartbox.atlassian.net/jira/software/projects/MFR2D2/boards/482)

# SETTING UP

## Requirements
- docker
- docker-compose
- python 2.7

## Instructions
1. clone the repository
2. execute ```bin/rc install```

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

## How to run R2D2 stub service
   - Navidate to R2-D2-API/stub
   - run docker-compose up
   - Hit http://localhost:8086/api/room_availabilities?roomId=11&startDate=2020-01-22&endDate=2020-02-09 in browser to test
