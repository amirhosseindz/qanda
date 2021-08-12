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
            ->expectsChoice('Please select one of the following actions', Action::Exit, Action::getValues())
            ->assertExitCode(0);

        $question = Question::query()->latest()->first();

        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals('how r u?', $question->question);
        $this->assertEquals('ok', $question->answer);
    }

    public function testHandleDelete(): void
    {
        $q = Question::store('how r u?', 'ok');

        $this->assertEquals(1, Question::count());

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::Delete, Action::getValues())
            ->expectsQuestion('Pick a question ID', $q->id)
            ->expectsChoice('Please select one of the following actions', Action::Exit, Action::getValues())
            ->assertExitCode(0);

        $this->assertEquals(0, Question::count());
    }

    public function testHandleList(): void
    {
        $q = Question::store('how r u?', 'ok');

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::List, Action::getValues())
            ->expectsTable(['ID', 'Question', 'Correct Answer'], [[$q->id, 'how r u?', 'ok']])
            ->expectsChoice('Please select one of the following actions', Action::Exit, Action::getValues())
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
            ->expectsQuestion('Pick a question ID', $q->id)
            ->expectsQuestion('Please give an answer to this question', 'nok')
            ->expectsOutput(AnswerStatus::Incorrect)
            ->expectsChoice('Continue?', 'Yes', ['Yes', 'No'])

            ->expectsTable(['ID', 'Question', 'Practice Status'], [
                [$q->id, 'how r u?', PracticeStatus::Incorrect],
                new TableSeparator(),
                ['-', 'Completion :', '0%']
            ])
            ->expectsQuestion('Pick a question ID', $q->id)
            ->expectsQuestion('Please give an answer to this question', 'ok')
            ->expectsOutput(PracticeStatus::Correct)
            ->expectsChoice('Continue?', 'No', ['Yes', 'No'])
            ->expectsChoice('Please select one of the following actions', Action::Exit, Action::getValues())
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
            ->expectsChoice('Please select one of the following actions', Action::Exit, Action::getValues())
            ->assertExitCode(0);
    }

    public function testHandleReset(): void
    {
        Answer::storeOrUpdate('nok', Question::store('how r u?', 'ok'));

        $this->assertEquals(1, Question::query()->count());
        $this->assertEquals(1, Answer::query()->count());

        $this->artisan('qanda:interactive')
            ->expectsChoice('Please select one of the following actions', Action::Reset, Action::getValues())
            ->expectsChoice('Please select one of the following actions', Action::Exit, Action::getValues())
            ->assertExitCode(0);

        $this->assertEquals(1, Question::query()->count());
        $this->assertEquals(0, Answer::query()->count());
    }
}
