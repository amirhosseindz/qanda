<?php

namespace App\Models;

use App\Enums\AnswerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

/**
 * @property int    $user_id To consider multiple user extensibility this is the user id of whom this answer belongs to
 * @property int    $question_id
 * @property string $answer
 * @property string $status
 */
class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'answer',
        'status'
    ];

    /**
     * @param string       $answer
     * @param AnswerStatus $status
     * @param Question     $question
     * @param int          $userId User id of whom this answer belongs to, since we don't have a User model we use "1"
     *                             as user id by default
     *
     * @return static
     */
    public static function storeOrUpdate(string $answer, AnswerStatus $status, Question $question, int $userId = 1): self
    {
        Assert::greaterThan($userId, 0, 'Invalid User Id');

        if ($oldAnswer = $question->findAnswer($userId)) {
            $oldAnswer->updateAnswer($answer, $status);

            return $oldAnswer;
        }

        return self::create([
            'user_id'     => $userId,
            'question_id' => $question->id,
            'answer'      => self::getValidatedAnswer($answer),
            'status'      => $status->value
        ]);
    }

    public function updateAnswer(string $answer, AnswerStatus $status): void
    {
        $this->answer = self::getValidatedAnswer($answer);
        $this->status = $status->value;

        if (! $this->save()) {
            throw new \RuntimeException('Could not update the answer in the database successfully');
        }
    }

    private static function getValidatedAnswer(string $answer): string
    {
        $answer = trim($answer);

        Assert::notEmpty($answer, 'Invalid Answer');

        return $answer;
    }
}
