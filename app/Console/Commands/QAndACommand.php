<?php

namespace App\Console\Commands;

use App\Enums\Action;
use App\Enums\AnswerStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Webmozart\Assert\InvalidArgumentException;

class QAndACommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Q&A app made with Laravel + Artisan';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $action = $this->choice('Please select one of the following actions', Action::getValues());

        $this->doAction(Action::make($action));
    }

    private function doAction(Action $action): void
    {
        switch ($action) {
            case Action::Create():
                $this->createQuestion();
                break;
            case Action::Delete():
                $this->deleteQuestion();
                break;
            case Action::List():
                $this->displayList();
                break;
            case Action::Practice():
                $this->practice();
                break;
            case Action::Stats():
                $this->displayStats();
                break;
            case Action::Reset():
                Answer::erase();
                break;
            case Action::Exit():
                return;
        }

        $this->handle();
    }

    private function checkIfAnyQuestionExists(): bool
    {
        if (! Question::query()->exists()) {
            $this->warn('No question found');

            return false;
        }

        return true;
    }

    private function createQuestion(): void
    {
        try {
            Question::store(
                $this->ask('Give a question') ?? '',
                $this->ask('Give the only answer') ?? ''
            );
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
            $this->createQuestion();
        }
    }

    private function deleteQuestion(): void
    {
        if (! $this->checkIfAnyQuestionExists()) {
            return;
        }

        $this->displayList();

        $question = $this->pickQuestion();

        if (! $question->deleteWithAnswers()) {
            $this->error('Could not delete the question successfully, please try again.');
        }
    }

    private function displayList(): void
    {
        $this->table(['ID', 'Question', 'Correct Answer'], Question::all(['id', 'question', 'answer']));
    }

    private function displayStats(): void
    {
        $totalCount = Question::query()->count();

        $this->line('- Total amount of questions : ' . $totalCount);

        $this->line('- Questions that have an answer : ' .
            $this->getPercent(Question::hasAnswer()->count(), $totalCount));

        $this->line('- Questions that have a correct answer : ' .
            $this->getPercent(Question::hasCorrectAnswer()->count(), $totalCount));
    }

    private function displayProgress(): void
    {
        $questions  = Question::all();
        $totalCount = $questions->count();

        $rows = $questions->map(static function (Question $question) {
            return [$question->id, $question->question, $question->getPracticeStatus()];
        })
            ->push(new TableSeparator())
            ->push(['-', 'Completion :', $this->getPercent(Question::hasCorrectAnswer()->count(), $totalCount)])
            ->toArray();

        $this->table(['ID', 'Question', 'Practice Status'], $rows);
    }

    private function practice(): void
    {
        if (! $this->checkIfAnyQuestionExists()) {
            return;
        }

        $this->displayProgress();
        if (! $question = $this->getQuestionToAnswer()) {
            return;
        }

        $this->printAnswerStatus($this->getAnswer($question)->getStatus());

        if ($this->choice('Continue?', ['Yes', 'No']) !== 'Yes') {
            return;
        }

        $this->practice();
    }

    private function getQuestionToAnswer(): ?Question
    {
        $question = $this->pickQuestion();

        if (($oldAnswer = $question->findAnswer()) && $oldAnswer->isCorrect()) {
            $this->warn('This question is already answered correctly');

            return $this->getQuestionToAnswer();
        }

        return $question;
    }

    private function pickQuestion(): Question
    {
        $id = $this->ask('Pick a question ID');

        if (! $question = Question::find($id)) {
            $this->error('Entered question ID is invalid');

            return $this->pickQuestion();
        }

        return $question;
    }

    private function getAnswer(Question $question): Answer
    {
        $answer = $this->ask('Please give an answer to this question');

        try {
           return Answer::storeOrUpdate($answer ?? '', $question);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return $this->getAnswer($question);
        }
    }

    private function printAnswerStatus(AnswerStatus $status): void
    {
        if ($status->isCorrect()) {
            $this->info($status->value);
        } else {
            $this->warn($status->value);
        }
    }

    private function getPercent(int $numerator, int $denominator): string
    {
        $percent = 0;
        if ($numerator !== 0 && $denominator !== 0) {
            $percent = number_format($numerator * 100 / $denominator);
        }

        return "$percent%";
    }
}
