## About Q&A

Q&A is an interactive CLI program for Question and Answer practice.
The artisan command `qanda:interactive` will present a main menu with the following
actions:
#### 1 . Create a question
The user will be prompted to give a question and the only answer to that question.
#### 2 . List all questions
A table listing all the created questions with the correct answer.
#### 3 . Practice
This is where a user will practice the questions that have been added. First, we will show the current progress. The user will be presented with a table listing all questions, and their practice status for each question: Not answered, Correct, Incorrect. As a table footer, we will present the % of completion (all questions vs correctly answered).

Then, the user will pick the question they want to practice. We will not allow answering questions that are already correct.
Upon answering, we will print correct/incorrect.
Finally, we will show the first step again (the current progress) and allow the user to keep practicing until they explicitly decide to stop.
#### 4 . Stats
We will display the following stats:
- The total amount of questions.
- % of questions that have an answer.
- % of questions that have a correct answer.
#### 5 . Reset
  This command will erase all practice progress and allow a fresh start.
#### 6 . Exit
  This option will conclude the interactive command.

## Installation

Note that Git control version and Docker engine must be already installed on your system.

#### 1 . Clone the project
```
git clone https://github.com/amirhosseindz/qanda.git
cd qanda
```
#### 2. Prepare the environment file
`cp .env.example .env`

Then fill the .env file with your own configuration values.
#### 3. Install the application's dependencies
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/opt \
    -w /opt \
    laravelsail/php80-composer:latest \
    composer install --ignore-platform-reqs
```
#### 4. Build the project
`./vendor/bin/sail up -d`
#### 5. Run the database migrations
`./vendor/bin/sail artisan migrate`
## Usage
Simply run `./vendor/bin/sail artisan qanda:interactive`
## Running tests
`./vendor/bin/sail test`
## License

Q&A is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
