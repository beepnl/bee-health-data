<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyword', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('dataset_keyword', function (Blueprint $table) {
            $table->uuid('dataset_id');
            $table->uuid('keyword_id');
            $table->integer('order');
            $table->timestamps();
            $table->foreign('dataset_id')->references('id')->on('dataset')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keyword');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dataset_keyword');
        Schema::dropIfExists('keyword');
    }
}
