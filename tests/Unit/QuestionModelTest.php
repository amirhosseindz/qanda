<?php

namespace Tests\Unit;

use App\Enums\PracticeStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class QuestionModelTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreSuccess()
    {
        $question = Question::store('how r u?', 'ok');

        $this->assertInstanceOf(Question::class, $question);
        $this->assertTrue($question->exists);
        $this->assertEquals('how r u?', $question->question);
        $this->assertEquals('ok', $question->answer);
    }

    public function testStoreEmptyQuestion()
    {
        $exception = null;
        $question  = null;

        try {
            $question = Question::store('    ', 'ok');
        } catch (\Throwable $exception) {
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid Question', $exception->getMessage());
        $this->assertNull($question);
    }

    public function testStoreEmptyAnswer()
    {
        $exception = null;
        $question  = null;

        try {
            $question = Question::store('how r u?', '    ');
        } catch (\Throwable $exception) {
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid Answer', $exception->getMessage());
        $this->assertNull($question);
    }

    public function testStoreInvalidUserId()
    {
        $exception = null;
        $question  = null;

        try {
            $question = Question::store('how r u?', 'ok', -1);
        } catch (\Throwable $exception) {
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid User Id', $exception->getMessage());
        $this->assertNull($question);
    }

    public function testFindAnswer(): void
    {
        $question = Question::store('how r u?', 'ok');

        Answer::storeOrUpdate('ok', $question);

        $answer = $question->findAnswer();

        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertEquals($question->id, $answer->question_id);
    }

    public function testFindAnswerWhenThereIsNoOne(): void
    {
        $question = Question::store('how r u?', 'ok');
        $answer   = $question->findAnswer();

        $this->assertNull($answer);
    }

    public function testFindAnswerLoadsQuestionRelation(): void
    {
        $q = Question::store('how r u?', 'ok');

        Answer::storeOrUpdate('nok', $q);

        $ans = $q->findAnswer();

        $this->assertTrue($ans->relationLoaded('question'));
        $this->assertInstanceOf(Question::class, $ans->question);
    }

    public function testGetPracticeStatus(): void
    {
        $question = Question::store('how r u?', 'ok');

        $this->assertEquals(PracticeStatus::NotAnswered, $question->getPracticeStatus()->value);

        Answer::storeOrUpdate('nok', $question);

        $this->assertEquals(PracticeStatus::Incorrect, $question->getPracticeStatus()->value);

        Answer::storeOrUpdate('ok', $question);

        $this->assertEquals(PracticeStatus::Correct, $question->getPracticeStatus()->value);
    }

    public function testDeleteWithAnswers(): void
    {
        $q = Question::store('how r u?', 'ok');
        Answer::storeOrUpdate('nok', $q);

        $this->assertEquals(1, Question::count());
        $this->assertEquals(1, Answer::count());

        $q->deleteWithAnswers();

        $this->assertEquals(0, Question::count());
        $this->assertEquals(0, Answer::count());
    }

    public function testDeleteWithAnswersWithNoAnswer(): void
    {
        $q = Question::store('how r u?', 'ok');

        $this->assertEquals(1, Question::count());

        $q->deleteWithAnswers();

        $this->assertEquals(0, Question::count());
    }
}
