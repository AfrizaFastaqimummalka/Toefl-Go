<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('section', 20)->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('available')->default(false);
            $table->unsignedTinyInteger('phase')->default(1);
            $table->json('recommend')->nullable();
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('kind', 20);
            $table->string('title');
            $table->unsignedSmallInteger('position')->default(0);
            $table->json('content')->nullable();
            $table->timestamps();
            $table->unique(['chapter_id', 'slug']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('section', 20)->index();
            $table->string('testing_point', 40)->index();
            $table->string('type', 30);
            $table->text('text');
            $table->json('options')->nullable();
            $table->json('segments')->nullable();
            $table->json('answer');
            $table->text('explanation');
            $table->json('hints');
            $table->json('xray')->nullable();
            $table->boolean('verified')->default(false);
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('source_page')->nullable();
            $table->timestamps();
        });

        Schema::create('vocab_cards', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40)->index();
            $table->string('group')->nullable();
            $table->string('term');
            $table->string('definition');
            $table->string('example')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocab_cards');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('chapters');
    }
};
