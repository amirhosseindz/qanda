<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Correct()
 * @method static static Incorrect()
 */
class AnswerStatus extends Enum
{
    public const Correct = 'Correct';
    public const Incorrect = 'Incorrect';
}
