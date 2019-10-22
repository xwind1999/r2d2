# R2D2 API

> A resourceful astromech droid, R2-D2 served PadméAmidala, Anakin Skywalker and Luke Skywalker in turn, showing great bravery in rescuing his masters and their friends from many perils.

## Main Responsibilities (domains):

* Enriches A&P (availability and pricing) data provided to JarvisBooking;
* Translates A&P data from Stay Business Data Model (CMHub and CMs, based on rooms and rate bands) to Smartbox Data Model (based on experiences and boxes);
* Enriches Bookings data provided to CMHub;
* Adds boxes and experiences information to Bookings that might be needed by the CMs;
* Routes bookings calls;
* Fetches A&P from CMHub when needed.

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

## High level tasks

- [x] Iterate over the first R2-D2 + C3-PO solution and provide an updated design
- [x] Receive feedback updated technical proposal
- [x] Based on the feedback, evolve the new proposal by conducing workshops with impacted systems
- [x] Present the consolidated proposal impacted systems, stakeholders and IT leadership
- [x] Draft a high level data model
- [x] Draft an initial database model to cover the MVP
- [x] Draft an initial API specification and design to cover the MVP
- [ ] Receive initial feedback about the drafted data model and API design
- [ ] Present the technical solution to the Architecture Board
- [ ] Break down the implementation epics
- [ ] Raise a delivery plan with Project Management taking into account the impacted systems and dependencies
- [ ] Bootstrap a team
- [ ] ...
