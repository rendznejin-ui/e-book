<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            // Restrict delete: a category with books cannot be removed until reassigned.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug', 280)->unique();
            $table->string('author');
            $table->string('isbn', 20)->nullable()->unique();
            $table->text('description')->nullable();
            // Money stored as integer cents — never floats.
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('stock_qty')->default(0);
            $table->string('cover_image')->nullable();
            $table->string('preview_pdf')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
