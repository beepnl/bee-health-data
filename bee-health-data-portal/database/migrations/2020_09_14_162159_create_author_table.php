<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('lastname');
            $table->string('initials');
            $table->string('organisation')->nullable();
            $table->timestamps();
        });

        Schema::create('dataset_author', function (Blueprint $table) {
            $table->uuid('dataset_id');
            $table->uuid('author_id');
            $table->integer('order');
            $table->foreign('dataset_id')->references('id')->on('dataset')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('author')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dataset_author');
        Schema::dropIfExists('author');
    }
}
