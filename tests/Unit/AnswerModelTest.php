<?php

namespace Tests\Unit;

use App\Enums\AnswerStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class AnswerModelTest extends TestCase
{
    use RefreshDatabase;

    public function testSuccessStore(): void
    {
        $q      = Question::store('how r u?', 'ok');
        $answer = Answer::storeOrUpdate('nok', $q);

        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertTrue($answer->exists);
        $this->assertEquals('nok', $answer->answer);
        $this->assertEquals(AnswerStatus::Incorrect, $answer->status);
        $this->assertEquals($q->id, $answer->question_id);
    }

    public function testSuccessUpdateAnswer(): void
    {
        $q      = Question::store('how r u?', 'ok');
        $answer = Answer::storeOrUpdate('nok', $q);

        $answer->updateAnswer('ok', AnswerStatus::Correct());

        $this->assertEquals('ok', $answer->answer);
        $this->assertEquals(AnswerStatus::Correct, $answer->status);
    }

    public function testStoreEmptyAnswer(): void
    {
        $q         = Question::store('how r u?', 'ok');
        $exception = null;
        $answer    = null;

        try {
            $answer = Answer::storeOrUpdate('   ', $q);
        } catch (\Throwable $exception) {
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid Answer', $exception->getMessage());
        $this->assertNull($answer);
    }

    public function testStoreInvalidUserId(): void
    {
        $q         = Question::store('how r u?', 'ok');
        $exception = null;
        $answer    = null;

        try {
            $answer = Answer::storeOrUpdate('nok', $q, -1);
        } catch (\Throwable $exception) {
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid User Id', $exception->getMessage());
        $this->assertNull($answer);
    }

    public function testUpdateAnswerEmptyAnswer(): void
    {
        $q         = Question::store('how r u?', 'ok');
        $answer    = Answer::storeOrUpdate('nok', $q);
        $exception = null;

        try {
            $answer->updateAnswer('   ', AnswerStatus::Correct());
        } catch (\Throwable $exception) {
        }

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Invalid Answer', $exception->getMessage());
        $this->assertEquals('nok', $answer->answer);
        $this->assertEquals(AnswerStatus::Incorrect, $answer->status);
    }

    public function testStoreAlwaysOneAnswerForAUser(): void
    {
        $q = Question::store('how r u?', 'ok');

        Answer::storeOrUpdate('nok', $q);
        Answer::storeOrUpdate('ok', $q);

        $this->assertEquals(1, $q->answers()->count());

        $answer = $q->findAnswer();

        $this->assertInstanceOf(Answer::class, $answer);
        $this->assertEquals('ok', $answer->answer);
        $this->assertEquals(AnswerStatus::Correct, $answer->status);
    }

    public function testErase(): void
    {
        $q = Question::store('how r u?', 'ok');

        Answer::storeOrUpdate('nok', $q);

        $this->assertEquals(1, $q->answers()->count());

        Answer::erase();

        $this->assertEquals(0, $q->answers()->count());
    }
}
