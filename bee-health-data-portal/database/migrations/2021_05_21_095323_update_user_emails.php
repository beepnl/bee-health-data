<?php

use App\Models\RegistrationInvitation;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UpdateUserEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = new User();
        $registrationInvitations = new RegistrationInvitation();

        foreach($users->get() as $user){
            $user->update(['email' => Str::lower($user->email)]);
        }

        foreach ($registrationInvitations->get() as $registrationInvitation) {
            $registrationInvitation->update(['email' => Str::lower($registrationInvitation->email)]);
        }
        $email = 'peter.neumann@vetsuisse.unibe.ch';
        foreach($users->ofEmail($email)->get() as $user){
            $user->delete();
        }
        $registrationInvitations->ofEmail($email)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
