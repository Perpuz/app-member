<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('author')->nullable()->change();
            $table->string('publisher')->nullable()->change();
            $table->integer('publication_year')->nullable()->change();
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->string('isbn')->nullable()->change();
        });
    }

    public function down(): void
    {
        
    }
};
