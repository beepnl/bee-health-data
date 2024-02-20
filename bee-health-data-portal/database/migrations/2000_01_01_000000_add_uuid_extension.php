<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUuidExtension extends Migration
{
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
    }

    public function down()
    {
        DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp";');
    }
}
