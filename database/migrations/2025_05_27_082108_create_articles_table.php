<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('title');
            $table->json('thumbnail')->default(json_encode([
                'original' => '#',
                'preview' => '#',
                'thumb' => '#',
            ]));
            $table->string('slug')->unique();
            $table->longText('body');
            $table->string('tags')->nullable();
            $table->enum('status', ['archived', 'draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('author_uuid')->references('uuid')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
