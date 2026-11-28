<?php

declare(strict_types=1);

namespace App\Enums;

enum QuizShowAnswersPolicy: string
{
    case AfterSubmission = 'after_submission';
    case AfterClosing = 'after_closing';
    case Never = 'never';

    public function label(): string
    {
        return match ($this) {
            self::AfterSubmission => 'Immediately After Submission',
            self::AfterClosing => 'After Quiz Availability Closes',
            self::Never => 'Never (Hide Answers)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AfterSubmission => 'Students immediately see correct answers and explanations after finishing.',
            self::AfterClosing => 'Answers remain hidden until the quiz deadline has passed for all students.',
            self::Never => 'Correct answers and explanations are never revealed to students.',
        };
    }
}
