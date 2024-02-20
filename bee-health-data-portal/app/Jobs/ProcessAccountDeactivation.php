<?php

namespace App\Jobs;

use App\Models\RegistrationInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessAccountDeactivation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        $registrationInvitations = new RegistrationInvitation;
        $user->update([
            'is_active' => false,
        ]);

        $registrationInvitations = $registrationInvitations->ofEmail($user->email);
        $registrationInvitations->delete();
        
        Storage::delete("exports/{$user->id}");
        $datasetMapping = function ($dataset) {
            $dataset->keywords()->detach();
            $dataset->files()->delete();
            $dataset->authors()->delete();
        };
        $user->datasets()->draft()->get()->map($datasetMapping);
        $user->datasets()->inactive()->get()->map($datasetMapping);
        $user->organisations()->detach();
        $user->datasets()->inactive()->delete();
        $user->datasets()->draft()->delete();
    }
}
