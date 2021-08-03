<?php

namespace App\Models;

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

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param string   $answer
     * @param Question $question
     * @param int      $userId User id of whom this answer belongs to, since we don't have a User model we use "1"
     *                         as user id by default
     *
     * @return static
     */
    public static function storeOrUpdate(string $answer, Question $question, int $userId = 1): self
    {
        if ($oldAnswer = $question->findAnswer($userId)) {
            return $oldAnswer->updateAnswer($answer);
        }

        Assert::greaterThan($userId, 0, 'Invalid User Id');

        $answer = self::getValidatedAnswer($answer);

        return self::create([
            'user_id'     => $userId,
            'question_id' => $question->id,
            'answer'      => $answer,
            'status'      => $question->getAnswerStatus($answer)->value
        ]);
    }

    public function updateAnswer(string $answer): self
    {
        $this->answer = self::getValidatedAnswer($answer);
        $this->status = $this->question->getAnswerStatus($this->answer)->value;

        if (! $this->save()) {
            throw new \RuntimeException('Could not update the answer in the database successfully');
        }

        return $this;
    }

    public static function erase(int $userId = 1): void
    {
        self::query()->user($userId)->delete();
    }

    private static function getValidatedAnswer(string $answer): string
    {
        $answer = trim($answer);

        Assert::notEmpty($answer, 'Invalid Answer');

        return $answer;
    }
}
