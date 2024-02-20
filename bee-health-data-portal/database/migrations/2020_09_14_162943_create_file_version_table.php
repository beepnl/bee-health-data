<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileVersionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_version', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('filename');
            $table->string('description');
            $table->string('file_format');
            $table->bigInteger('size');
            $table->integer('version');
            $table->timestamps();
        });

        Schema::create('dataset_file', function (Blueprint $table) {
            $table->uuid('file_version_id');
            $table->uuid('dataset_id');
            $table->timestamps();
            $table->foreign('file_version_id')->references('id')->on('file_version')->onDelete('cascade');
            $table->foreign('dataset_id')->references('id')->on('dataset')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dataset_file');
        Schema::dropIfExists('file_version');
    }
}
