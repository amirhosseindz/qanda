<?php

namespace Tests\Feature;

use App\Enums\Action;
use App\Enums\AnswerStatus;
use App\Enums\PracticeStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Helper\TableSeparator;
use Tests\TestCase;

class QAndACommandTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleCreateQuestion(): void
    {
        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::Create, Action::getValues())
            ->expectsQuestion('Give a question', 'how r u?')
            ->expectsQuestion('Give the only answer', 'ok')
            ->assertExitCode(0);

        $question = Question::query()->latest()->first();

        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals('how r u?', $question->question);
        $this->assertEquals('ok', $question->answer);
    }

    public function testHandleList(): void
    {
        $q = Question::store('how r u?', 'ok');

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::List, Action::getValues())
            ->expectsTable(['Question', 'Correct Answer'], [['how r u?', 'ok']])
            ->assertExitCode(0);
    }

    public function testHandlePractice(): void
    {
        $q = Question::store('how r u?', 'ok');

        $this->assertEquals(0, Answer::query()->count());

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::Practice, Action::getValues())

            ->expectsTable(['ID', 'Question', 'Practice Status'], [
                [$q->id, 'how r u?', PracticeStatus::NotAnswered],
                new TableSeparator(),
                ['-', 'Completion :', '0%']
            ])
            ->expectsQuestion('Pick an ID to practice or enter "0" to exit', $q->id)
            ->expectsQuestion('Please give an answer to this question', 'nok')
            ->expectsOutput(AnswerStatus::Incorrect)
            ->expectsChoice('Continue?', 'Yes', ['Yes', 'No'])

            ->expectsTable(['ID', 'Question', 'Practice Status'], [
                [$q->id, 'how r u?', PracticeStatus::Incorrect],
                new TableSeparator(),
                ['-', 'Completion :', '0%']
            ])
            ->expectsQuestion('Pick an ID to practice or enter "0" to exit', $q->id)
            ->expectsQuestion('Please give an answer to this question', 'ok')
            ->expectsOutput(PracticeStatus::Correct)
            ->expectsChoice('Continue?', 'Yes', ['Yes', 'No'])

            ->expectsTable(['ID', 'Question', 'Practice Status'], [
                [$q->id, 'how r u?', PracticeStatus::Correct],
                new TableSeparator(),
                ['-', 'Completion :', '100%']
            ])
            ->expectsQuestion('Pick an ID to practice or enter "0" to exit', '0')

            ->assertExitCode(0);

        $this->assertEquals(1, Answer::query()->count());
    }

    public function testHandleStats(): void
    {
        Answer::storeOrUpdate('nok', Question::store('how r u?', 'ok'));

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::Stats, Action::getValues())
            ->expectsOutput('- Total amount of questions : 1')
            ->expectsOutput('- Questions that have an answer : 100%')
            ->expectsOutput('- Questions that have a correct answer : 0%')
            ->assertExitCode(0);
    }

    public function testHandleReset(): void
    {
        Answer::storeOrUpdate('nok', Question::store('how r u?', 'ok'));

        $this->assertEquals(1, Question::query()->count());
        $this->assertEquals(1, Answer::query()->count());

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::Reset, Action::getValues())
            ->assertExitCode(0);

        $this->assertEquals(1, Question::query()->count());
        $this->assertEquals(0, Answer::query()->count());
    }
}
