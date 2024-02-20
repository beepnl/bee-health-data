<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateOrganisationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_bgood_partner')->default(false);
            $table->timestamps();
        });

        Schema::create('organisation_user', function (Blueprint $table) {
            $table->uuid('organisation_id');
            $table->uuid('user_id');
            $table->uuid('user_role')->nullable();
            $table->timestamps();
            $table->foreign('organisation_id')->references('id')->on('organisation')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('user_role')->references('id')->on('user_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organisation_user');
        Schema::dropIfExists('organisation');
    }
}
