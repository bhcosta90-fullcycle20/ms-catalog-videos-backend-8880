<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryGenreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('category_id')->constrained('categories');
            $table->foreignUuid('genre_id')->constrained('genres');
            $table->timestamps();
            $table->unique(['category_id', 'genre_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_genre');
    }
}
