# [Testing pyramid](https://martinfowler.com/articles/practical-test-pyramid.html#TheTestPyramid)
## Unit test (Phpunit is used to write unit tests)
- Business logic has to be tested at unit level.
- Mock dependencies and try to cover as mush as possible at unit level.
## Contract tests (pact.io is used to write contract tests)
- Data models(expectations from client) should be tested at contract tests level
- R2D2 will be provider to different systems(JB,EAI)
- Contract should be defined by consumers(JB,EAI) and has to be fulfilled by R2D2.
- R2D2 stub should be update as per contract defined by JB,EAI.
## API tests (Phpunit is used to write Api tests)
-  Add one positive and one negative test at api level
   - example - get availability with right data as per contract should return 200 
   - example - get availability with invalid query string parameters should return 400
   - Rest all should be tested at unit/contract tests
- Every api should have api test to test controller over http/https.
## End2End tests (Phpunit is used to write end2end tests)
- These tests will be more business focused. 
- Example - GetAvailability is called by JB with boxId and date range
- Example - GetAvailability is called by JB with experienceId date range
- Example - GetAvailability is called by JB with roomId and date range
- Example - Create booking for 1 night
- Example - Create booking for 2 nights
- Example - Create booking with up sell
- Example - GetAvailability for the same room after booking is created.
          
## Load tests (Locust is used to write load tests)
- Add load test for every Api(Based on NFR)
- Add end2End load test (Listen to EAI broadcast,respond to getAvailability and createBooking)

# Test automation tools

## pact.io (This library is used for contract testing). [Explore pact](https://docs.pact.io/)
## Locust (This tool is used for load/performance testing). [Explore locust](https://docs.locust.io/en/stable/)
- Locust Load Tests are run both in Devint and Preprod environments
- Command to run locust tests: 
 -- `locust -f locustfile  --host=hostname --headless -u 50 -r 5 --run-time 1m --only-summary`
 
## R2D2 stub service (This service will be used by Jervis Booking/EAI for testing in project environment)
- Run R2D2 stub service on local
    - Navigate to /stub
    - run docker-compose up
    - Test - [Click here](http://localhost:8086/api/room_availabilities?roomId=11&startDate=2020-01-22&endDate=2020-02-09)
    - Availabilities should be returned.
- Update stub service
    - Navigate to stub/sandbox/main.js
    - Add/update new api call
    - Commit your changes
    - Stub service should be aligned with R2D2 API
