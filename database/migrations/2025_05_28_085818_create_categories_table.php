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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('descriptions')->nullable();
            $table->uuid('parent_uuid')->nullable();
            $table->foreignUuid('creator_uuid')->references('uuid')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('articles_categories', function (Blueprint $table) {
            $table->foreignUuid('article_uuid')->references('uuid')->on('articles')->cascadeOnDelete();
            $table->foreignUuid('category_uuid')->references('uuid')->on('categories')->cascadeOnDelete();
            $table->primary(['article_uuid', 'category_uuid']);
            $table->unique(['article_uuid', 'category_uuid']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles_categories');
        Schema::dropIfExists('categories');
    }
};
