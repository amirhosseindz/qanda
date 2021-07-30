<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

/**
 * @property string $question
 * @property string $answer
 * @property int    $created_by To consider multiple user extensibility this is the user id of who created this question
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'created_by',
    ];

    /**
     * @param string $question
     * @param string $answer Correct answer to this question
     * @param int    $createdBy User id of who is creating this question, since we don't have a User model we use "1"
     *                          as user id by default
     *
     * @return static
     */
    public static function store(string $question, string $answer, int $createdBy = 1): self
    {
        $question = trim($question);
        $answer   = trim($answer);

        Assert::notEmpty($question);
        Assert::notEmpty($answer);
        Assert::greaterThan($createdBy, 0);

        return self::create([
            'question'   => $question,
            'answer'     => $answer,
            'created_by' => $createdBy
        ]);
    }
}
