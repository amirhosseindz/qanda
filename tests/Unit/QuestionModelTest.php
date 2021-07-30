<?php

namespace Tests\Unit;

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
}
