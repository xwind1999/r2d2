# CI/CD

## Stages
- Build base image

  This step runs only for specific branches, where we need to build a new base image for our project.
  Usually you don't need to worry about it.

- Build

  Here we run composer install
  
- Test
  
  In this step, we run Unit Tests, contract tests against the stub, contract tests against the api, api tests, and we also validate the api schema.
  We should aim for this step to be as small as possible, but at the same time not lacking tests.
  
- Test Coverage Analysis
  
  Here we run infection against our code, to see how well covered we are. We also run SonarQube against the code. 

- Static analysis

  This step runs PHPStan, PSalm, PHP CS Fixer and phpmetrics. 

- Build Api Reference

  Here we generate the api reference for the documentation, using a Symfony command

- Build Reports

  In this step, we gather data from previous steps and prepare some reports:
  * It sends the code and coverage to SonarQube
  * It generates some documentation images using mermaid
  * It also merges some reports into a single artifact output (for use with the next step - publish reports) 

- Publish Reports

  This step should run only for master. It gets the report from the previous steps and publishes it as our documentation.

## Do's and don'ts

* DO NOT build a docker image if you're not going to deploy it.
* DO NOT run slow tests on merge-requests pipelines or feature branches
