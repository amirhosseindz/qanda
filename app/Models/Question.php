<?php

namespace App\Models;

use App\Enums\AnswerStatus;
use App\Enums\PracticeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

/**
 * @property int    $id
 * @property string $question
 * @property string $answer
 * @property int    $created_by To consider multiple user extensibility this is the user id of who created this question
 *
 * @method static Builder hasAnswer(int $userId = 1)
 * @method static Builder hasCorrectAnswer(int $userId = 1)
 * @method static Question find(int $id, array $columns = [])
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'created_by',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function scopeHasAnswer($query, int $userId = 1)
    {
        return $query->whereHas('answers', function ($q) use ($userId) {
            $q->user($userId);
        });
    }

    public function scopeHasCorrectAnswer($query, int $userId = 1)
    {
        return $query->whereHas('answers', function ($q) use ($userId) {
            $q->user($userId)->status(AnswerStatus::Correct());
        });
    }

    public function findAnswer(int $userId = 1): ?Answer
    {
        $answers = $this->answers()->user($userId)->get();
        if ($answers->count() > 1) {
            throw new \RuntimeException('Question can not has more than one answer for a single user');
        }
        if ($answer = $answers->first()) {
            $answer->setRelation('question', $this);
        }

        return $answer;
    }

    /**
     * @param string $question
     * @param string $answer    Correct answer to this question
     * @param int    $createdBy User id of who is creating this question, since we don't have a User model we use "1"
     *                          as user id by default
     *
     * @return static
     */
    public static function store(string $question, string $answer, int $createdBy = 1): self
    {
        $question = trim($question);
        $answer   = trim($answer);

        Assert::notEmpty($question, 'Invalid Question');
        Assert::notEmpty($answer, 'Invalid Answer');
        Assert::greaterThan($createdBy, 0, 'Invalid User Id');

        return self::create([
            'question'   => $question,
            'answer'     => $answer,
            'created_by' => $createdBy
        ]);
    }

    public function getPracticeStatus(int $userId = 1): PracticeStatus
    {
        if ($answer = $this->findAnswer($userId)) {
            return new PracticeStatus($answer->status);
        }

        return PracticeStatus::NotAnswered();
    }

    public function getAnswerStatus(string $answer): AnswerStatus
    {
        return $answer === $this->answer ? AnswerStatus::Correct() : AnswerStatus::Incorrect();
    }

    public function deleteWithAnswers(): bool
    {
        return $this->deleteAnswers() && $this->delete();
    }

    public function deleteAnswers(): bool
    {
        if (! $this->answers()->exists()) {
            return true;
        }

        return $this->answers()->delete();
    }
}
