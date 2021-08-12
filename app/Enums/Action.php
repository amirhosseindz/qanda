<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Create()
 * @method static static Delete()
 * @method static static List()
 * @method static static Practice()
 * @method static static Stats()
 * @method static static Reset()
 * @method static static Exit()
 */
class Action extends Enum
{
    public const Create = 'Create a question';
    public const Delete = 'Delete a question';
    public const List = 'List all questions';
    public const Practice = 'Practice';
    public const Stats = 'Stats';
    public const Reset = 'Reset';
    public const Exit = 'Exit';

    /**
     * @param string $action
     *
     * @return static
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumMemberException
     */
    public static function make(string $action): self
    {
        return new self($action);
    }
}
