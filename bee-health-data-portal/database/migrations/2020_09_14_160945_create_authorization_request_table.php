<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorizationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Confirm for delete
        // Schema::create('authorization_type', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->string('name');
        //     $table->timestamps();
        // });

        Schema::create('authorization_request', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('response_note')->nullable();
            $table->timestamp('requested_at');
            $table->uuid('requesting_user_id');
            $table->uuid('requesting_organisation_id')->nullable();
            $table->uuid('requesting_dataset_id')->nullable();
            // Confirm for update to string
            $table->string('authorization_type');
            // $table->string('authorization_type_id');
            $table->timestamps();
            $table->foreign('requesting_user_id')->references('id')->on('users');
            $table->foreign('requesting_organisation_id')->references('id')->on('organisation');
            $table->foreign('requesting_dataset_id')->references('id')->on('dataset')->onDelete('cascade');
            // Confirm for delete
            // $table->foreign('authorization_type_id')->references('id')->on('authorization_type');
        });

        Schema::create('authorization', function (Blueprint $table) {
            $table->uuid('authorization_request_id')->nullable();
            $table->uuid('organisation_id')->nullable();
            $table->uuid('dataset_id');
            $table->uuid('user_id')->nullable();
            $table->timestamps();
            $table->foreign('authorization_request_id')->references('id')->on('authorization_request');
            $table->foreign('organisation_id')->references('id')->on('organisation')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('dataset_id')->references('id')->on('dataset')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authorization');
        Schema::dropIfExists('authorization_request');
        Schema::dropIfExists('authorization_type');
    }
}
