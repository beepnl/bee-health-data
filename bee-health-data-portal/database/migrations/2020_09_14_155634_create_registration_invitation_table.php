<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrationInvitationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registration_invitation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('token', 100);
            $table->timestamp('expires_at');
            $table->uuid('user_role_id');
            $table->uuid('organisation_id');
            $table->timestamps();
            $table->foreign('user_role_id')->references('id')->on('user_role');
            $table->foreign('organisation_id')->references('id')->on('organisation')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registration_invitation');
    }
}
