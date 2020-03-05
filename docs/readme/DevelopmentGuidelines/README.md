### R2D2 CODING STANDARDS AND DEVELOPMENT GUIDELINES

The following document intents to share information on how we must develop code, in order to try to d reduce any conflict within the R2D2 project.

#### GUIDELINES AND CONVENTIONS

##### General Standards:
- You should write code according to PSRs and try to run PHPCS to check if code is developed following any of those recommendations.
    - [PSR-1](https://www.php-fig.org/psr/psr-1/)
    - [PSR-12](https://www.php-fig.org/psr/psr-12//)
    - [PSR-4](https://www.php-fig.org/psr/psr-4/)
    - [Symfony Coding Standards](https://symfony.com/doc/current/contributing/code/standards.html)
    - [PEAR Coding Standards](https://pear.php.net/manual/en/standards.php)
- When coding always try to use SOLID Concepts to guarantee a maintanable and extandable code.
    - Single-responsibility principle – A class should be responsible for only one task.
    - Open-closed principle – A class should be open to extension and close to modification.
    - Liskov substitution principle – A derived class can be substituted at places where the base class is used.
    - Interface segregation principle – Create short interfaces and do not override new agreements that are not needed for a specific class.
    - Dependency inversion principle – Depend on abstractions, not on concretions.
        - Read more about: [SOLID examples](https://github.com/wataridori/solid-php-example)
- Always try to use Design Patterns when coding, those are the representation of the best practices to solve common object-oriented problems.
    - [Collection of Design Patterns](https://designpatternsphp.readthedocs.io/en/latest/README.html)
    - [Design Patterns Repository](https://github.com/domnikl/DesignPatternsPHP)

##### Testing

- Create small unit tests - single responsibility per test 
- Make one assert per test - that way; we guarantee only one thing being tested.
- Create a meaningful name for classes and methods that describes what is expected from it
- Think in the unit tests like production code
- (DRY) Don't Repeat Yourself
- Avoid conditional statements inside a test
- Try to use "new()" only to instantiate the class is being tested
- Do not put hard-coded values inside the unit test, unless it has a useful meaning
- Unit tests must be stateless - stateful tests can cause false positives.

Read more about: 
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Additional Unit Tests Guidelines](https://petroware.no/unittesting.html)

##### YAML Standards

- Files should end in _.yaml_
- Use Snake Case
- Do not use tabs, use spaces instead
- Use _mappings_, _sequences_ and _scalars_ data types when necessary

Read more about: [YAML Documentation](https://yaml.org/)

##### Code Documentation

- Describe "How" something works instead of "What" it does
- Use DRY concept (Don't Repeat Yourself)
- Code and document, avoid documenting after a long time
- Document for humans, not for machines
- Comment every property class and method with the corresponding parameters type and name, return type and possible exceptions
- Do not need to comment every single line, use comments to explain something that it is requires to be explained

Read about: [PHPDoc Documentation](https://www.phpdoc.org/)

##### GIT Strategy
- Always review code before committing
- Create a branch with the same ticket's name based on the **master** branch
- Never rebase shared commits
- Never delete unmerged remote branches
- Always commit your changes, do not wait until your task is done to commit, pay attention to the following rules:
    - Always commit when you have:
        - Wrote the first draft of a new class
        - Wrote a new piece of unit test
        - Before experimenting something, commit the previous work
- Push your changes every day
    - It brings visibility for everyone
    - In case something goes wrong, you will have the work saved
- When you are going to create an MR, rebase it before sending to code review
- When working in an already created MR, always use the words **WIP** (Work In Progress) in case the work has been finished yet
- Always mark to delete your branch when the MR is complete
- Always mark to squash the commits in a recently created MR
        
 
###### Git Message Model
- `git commit -m "[R2D2-XYZ] Do something"`

##### Code Review Best Practices
Take those rules as a consideration when developing:

- Read the full ticket to understand the functionalities and requirements
- Validate if the branch has the same ticket name
- Check if the MR title has the following pattern:
    **[R2D2-XYZ] Do something**
- Validate if the boxes squash _Delete source branch when merge request is accepted_ and _Squash commits when merge request is accepted_ are marked 
- Identify if any part of the code can cause an issue in production
- Analyse if the code developed is aligned with the ticket' requirements
- Validate if we have migrations in case of new fields/entities
- Look for duplicated code
- Check if the code is well-designed
- Code must not be complex than necessary
- Coverage must be of 100% of anything new
- Unit tests are developed according to our recommendations
- It has been used a meaningful and clear name for classes, methods, variables, etc
- There are properly comments
- Additional suggestions:
    - Review fewer than 400 lines of code at a time
    - Take your time. Inspection rates should under 500 LOC per hour
        - Source: [Code Review Report](https://smartbear.com/resources/ebooks/the-state-of-code-review-2019/)

##### DIRECTORY STRUCTURE

We must keep the same structure behaviour and avoid creating new directories unless it is necessary. 

Currently, those are the main directories used:
- `/.ci`    - contains the YAML files responsible for managing the Gitlab CI flow.
- `/config`   - contains the bootstrap, Yaml files, routes and project’s Packages.
- `/docker`   - environment -> contains the docker files and relates files to build and start the docker containers.
- `/docs`     - contains any additional documents, as diagrams, flows, readmes about the project.
- `/src`      - contains the project files, such as Entities, Controller, Interfaces, etc.
- `/reference`- contains the YAML files with API descriptions, properties, etc
- `/tests`    - contains every tests class used to check code and application health

##### APPLICATION HEALTH CHECK

We provide a way to run a series of application related health checks for the PHP Composer Libraries Security Advisory before and after deploying the application. In case of something fails, we should raise a ticket to check and fix it. 


##### R2D2 UPDATE SCHEDULES

Many harmful malware attacks that we see currently take advantage of failures found in older versions of programming language and frameworks. Because of that and in order to to keep a safe, fast and reliable application, we adopt a scheduled routine to maintain the application updated as following:

###### PHP Version
Expected date: **January**<br>
[PHP Releases Dates](https://www.php.net/supported-versions.php)

###### Framework
Expected date: **November**<br>
[Symfony Releases Dates](https://symfony.com/releases)

If you have any query, always post into Matermost channel to discuss it with your team.
