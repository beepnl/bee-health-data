<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLicenseIdDatasetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dataset', function (Blueprint $table) {
            $table->uuid('license_id')->nullable();
            $table->foreign('license_id')->references('id')->on('license')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dataset', function (Blueprint $table) {
            $table->dropColumn('license_id');
        });
    }
}
