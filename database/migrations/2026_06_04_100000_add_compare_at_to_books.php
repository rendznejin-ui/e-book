<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Original "list price" in integer cents. When set and higher than
            // price_cents, the book is on sale and we show the markdown.
            $table->unsignedInteger('compare_at_cents')->nullable()->after('price_cents');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('compare_at_cents');
        });
    }
};
