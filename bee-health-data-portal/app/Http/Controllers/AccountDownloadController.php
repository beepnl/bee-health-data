<?php

namespace App\Http\Controllers;


use App\Http\Requests\AccountDownloadRequest;
use App\Jobs\ProcessAccountDataExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class AccountDownloadController extends Controller
{
    public function export()
    {
        ProcessAccountDataExport::dispatch(Auth::user());
        return redirect()->route('home')->with('status', 'You have a new email for export account data.');
    }

    public function download(AccountDownloadRequest $request)
    {
        $user_id = $request->route('id');
        
        return response()->streamDownload(function () use ($user_id) {
            $opt = new Archive();
            $opt->setContentType('application/octet-stream');
            $opt->setZeroHeader(true);
            $zip = new ZipStream("personal_account_data.zip", $opt);
            $xlsx_files = Storage::allFiles("exports/{$user_id}/");

            foreach ($xlsx_files as $xlsx_file) {
                $file = Storage::readStream($xlsx_file);
                $zip->addFileFromStream(basename($xlsx_file), $file);
            }

            $zip->finish();
        }, "personal_account_data.zip");
    }
}
