<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('answer')->nullable();
            $table->boolean('correct');
            $table->unsignedTinyInteger('hints_used')->default(0);
            $table->unsignedInteger('time_ms')->nullable();
            $table->string('mode', 20)->default('practice');
            $table->timestamp('answered_at')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'question_id']);
            $table->index(['user_id', 'answered_at']);
        });

        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('section', 20);
            $table->string('testing_point', 40);
            $table->unsignedInteger('attempted')->default(0);
            $table->unsignedInteger('correct')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'testing_point']);
        });

        Schema::create('review_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('box')->default(0);
            $table->timestamp('due_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'due_at']);
        });

        Schema::create('essays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->text('body');
            $table->unsignedSmallInteger('word_count')->default(0);
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('feedback')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 20);
            $table->string('question_code')->nullable();
            $table->unsignedSmallInteger('status')->default(200);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
        Schema::dropIfExists('essays');
        Schema::dropIfExists('review_queue');
        Schema::dropIfExists('progress');
        Schema::dropIfExists('attempts');
    }
};
