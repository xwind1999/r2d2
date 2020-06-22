# R2D2 API

> A resourceful astromech droid, R2-D2 served Padmé Amidala, Anakin Skywalker and Luke Skywalker in turn, showing great bravery in rescuing his masters and their friends from many perils.

## Master branch Gitlab Status:
![pipeline status](http://gitlab.production.smartbox.com/millenniumfalcon/r2-d2-api/badges/master/pipeline.svg)
![coverage report](http://gitlab.production.smartbox.com/millenniumfalcon/r2-d2-api/badges/master/coverage.svg)

##  Reports and generated documentation
* [Main Documentation](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api)
* [Database Schema](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/report/html/db)
* [Unit Tests Coverage Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/report/html/phpunit/)
* [PHPMetrics Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/report/html/phpmetrics/)
* [Api Tests Coverage Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/report/html/api-tests/)
* [Locust DEVINT Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/report/html/locustreport/result.html)
* [Locust PREPROD Report](http://millenniumfalcon.gitlab.production.smartbox.com/r2-d2-api/report/html/locustreport/result.html)

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
