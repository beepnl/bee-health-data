<?php

namespace App\Jobs;

use App\Exports\AccountExport;
use App\Exports\FilesExport;
use App\Exports\MyAccessRequestsExport;
use App\Exports\MyDatasetsExport;
use App\Models\User;
use App\Notifications\AccountDataNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessAccountDataExport implements ShouldQueue
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
        $directory_export = 'exports';
        $directory_user = "{$directory_export}/{$user->id}";

        if (!Storage::exists("{$directory_export}")) {
            Storage::makeDirectory($directory_export);
        }

        if (!Storage::exists("{$directory_user}")) {
            Storage::makeDirectory("{$directory_user}");
        }

        (new AccountExport)->forUser($user->id)->store("{$directory_user}/account_data.xlsx");
        (new MyDatasetsExport)->forUser($user->id)->store("{$directory_user}/my_datasets.xlsx");
        (new MyAccessRequestsExport)->forUser($user->id)->store("{$directory_user}/my_access_requests.xlsx");
        (new FilesExport)->forUser($user->id)->store("{$directory_user}/my_files.xlsx");

        $user->notify(new AccountDataNotification);
    }
}
