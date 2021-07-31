<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', static function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id');
            $table->foreignId('question_id');
            $table->string('answer');
            $table->string('status');

            $table->timestamps();

            $table->unique(['user_id', 'question_id']);
            $table->foreign('question_id')->on('questions')->references('id')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('answers');
    }
}
