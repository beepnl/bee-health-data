<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileRequest;
use App\Models\Dataset;
use App\Models\FileVersion;
use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\VarDumper\Cloner\Data;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;
// use Aws\S3\MultipartUploader;

class FileController extends Controller
{
    const TIME_LIMIT = 1800;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(FileRequest $request)
    {
        set_time_limit(self::TIME_LIMIT);
        $dataset = Dataset::findOrFail($request->dataset_id);
        if(!$dataset->is_downloadable){
            abort(401);
        }
        $files = $dataset->files;
        $dataset_path = "datasets/{$request->dataset_id}";
        $zipFilename = '';
        foreach ($files as $file) {
            $zipFilename .= $file->id;
        }
        $zipFilename = md5($zipFilename);
        $storageDriver = Storage::disk('s3')->getDriver();
        $adapter = $storageDriver->getAdapter();
        $s3Client = $adapter->getClient();
        $s3Client->registerStreamWrapper(); 

        return response()->streamDownload(function () use ($files, $dataset_path, $dataset) {
            $opt = new Archive();
            $opt->setContentType('application/octet-stream');
            $opt->setZeroHeader(true);
            $zip = new ZipStream(Str::slug("{$dataset->name}") . ".zip", $opt);

            foreach ($files as $_file) {
                $file = Storage::readStream("{$dataset_path}/{$_file->id}.{$_file->file_format}");
                $_filename = Str::slug($_file->filename) . ".{$_file->file_format}";
                $relative_path = "{$dataset->id}/{$_filename}";
                $zip->addFileFromStream($relative_path, $file);
            }

            $zip->finish();
        }, Str::slug("{$dataset->name}") . ".zip");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FileRequest $request)
    {
        $dataset = Dataset::findOrFail($request->dataset_id);

        if (!$dataset->is_editable) {
            abort(401);
        }
        $version = 1;
        $bucket = env('AWS_BUCKET');
        $file = $request->file('file');
        $filename = $request->input('filename');
        $fileVersionId = $request->input('id');
        $partNumber = (int)$request->input('partNumber', 0);

        if(!$fileVersionId){
            $fileVersionId = Uuid::uuid1()->toString();
        }
        
        $pathinfo = pathinfo(basename($file->getClientOriginalName(), '.part'));
        $extension = $pathinfo['extension'];
        $keyFilename = "{$fileVersionId}.{$extension}";
        $key = "datasets/{$dataset->id}/{$keyFilename}";

        $s3Client = new S3Client([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest'
        ]);
        
        if($request->input('uploadId') == "null"){
            $result = $s3Client->createMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
            $uploadId = $result['UploadId'];
        }else{
            $uploadId = $request->input('uploadId');
        }

        $result = $s3Client->uploadPart([
            'Bucket' => $bucket,
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
            'ContentLength' => strlen($file->get()),
            'Body' => $file->get(),
        ]);
            
        // Create Multipart Upload - https://docs.aws.amazon.com/AmazonS3/latest/API/API_CreateMultipartUpload.html
        // uploadPart - https://docs.aws.amazon.com/AmazonS3/latest/API/API_UploadPart.html
        // Complete Multipart Upload - https://docs.aws.amazon.com/AmazonS3/latest/API/API_CompleteMultipartUpload.html
        // Abort Multipart Upload - https://docs.aws.amazon.com/AmazonS3/latest/API/API_AbortMultipartUpload.html
        // List Parts - https://docs.aws.amazon.com/AmazonS3/latest/API/API_ListParts.html
        // List Multipart Uploads - https://docs.aws.amazon.com/AmazonS3/latest/API/API_ListMultipartUploads.html
        
        if ($request->has('is_last_blob') && $request->boolean('is_last_blob')) {
            $extension = $pathinfo['extension'];
            $partsModel = $s3Client->listParts(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
            ));

            $result = $s3Client->completeMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $partsModel['Parts']
                ]
            ]);
            
            if($fileVersion = FileVersion::find($fileVersionId)){
                $version = $fileVersion->nextVersion;
                $fileVersion->update([
                    'file_format' => $extension,
                    'size' => Storage::size($key),
                    'version' => $version
                ]);
            }else{
                $fileVersionId = FileVersion::create([
                    'id' => $fileVersionId,
                    'filename' => $request->filename,
                    'description' => $request->description,
                    'file_format' => $extension,
                    'size' => Storage::size($key),
                    'version' => 1,
                ])->id;
                $files = $dataset->files();
                $files->attach($fileVersionId);
                $dataset->update(['number_files' => $files->count()]);
            }

        }

        return $request->wantsJson()
            ? new JsonResponse([
                'size' => Storage::exists($key) ? Storage::size($key) : 0, 
                'path' => $key, 
                'completed' => $request->boolean('is_last_blob'), 
                'id' => $fileVersionId, 
                'version' => $version,
                'partNumber' => $partNumber,
                'uploadId' => $uploadId,
                'filename' => $filename,
            ])
            : response(true);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FileVersion $file_version)
    {
        set_time_limit(self::TIME_LIMIT);
        $dataset = $file_version->datasets()->first();

        if (!$dataset->is_downloadable) {
            abort(401);
        }
        $name = Str::slug($file_version->filename) . ".{$file_version->file_format}";
        $stream = Storage::readStream("datasets/{$dataset->id}/{$file_version->id}.{$file_version->file_format}");

        if (ob_get_level()) ob_end_clean();

        return response()->streamDownload(function() use ($stream){

            while (!feof($stream)) {
                echo fread($stream, 2048);
            }

            fclose($stream);

        }, $name);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FileRequest $request, $id)
    {
        $fileVersion = FileVersion::findOrFail($id);
        $dataset = $fileVersion->datasets()->first();
        if (!$dataset->is_editable) {
            abort(401);
        }

        $fileVersion->update([
            'filename' => $request->filename,
            'description' => $request->description
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FileRequest $request, $id)
    {
        $fileVersion = FileVersion::findOrFail($id);
        $dataset = $fileVersion->datasets()->first();
        if (!$dataset->is_editable) {
            abort(401);
        }
        $fileVersion->datasets()->detach($request->dataset_id);
        $path = "datasets/{$request->dataset_id}/{$fileVersion->id}.{$fileVersion->file_format}";
        $fileVersion->delete();
        Storage::delete($path);

        $dataset = Dataset::findOrFail($request->dataset_id);
        $dataset->update(['number_files' => $dataset->files->count()]);

        if(empty(Storage::files(dirname($path)))){
            Storage::deleteDirectory(dirname($path));
        }

        return new JsonResponse('', 204);
    }
}
