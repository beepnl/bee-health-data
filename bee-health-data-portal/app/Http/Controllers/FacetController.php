<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\FileVersion;
use App\Models\Keyword;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $resource)
    {
        $query_string = $request->input('query', '');
        $limit = 10;

        if($resource === 'keywords'){
            $keywords = Keyword::whereHas('datasets', function ($q) use ($query_string) {
                $q->published();
                if(!auth()->user()){
                    $q->openAccess();
                }
            })->where('name', 'ilike', "%{$query_string}%")->take($limit)->get();
            return new JsonResponse($keywords->unique('name')->values()->all());
        }

        if ($resource === 'authors') {
            $authors = Author::whereHas('datasets', function ($q) use ($query_string) {
                $q->published();
                if(!auth()->user()){
                    $q->openAccess();
                }
            })->where(DB::raw('concat(lastname, \', \', initials)'), 'ilike', "%{$query_string}%")->distinct(DB::raw('concat(lastname, \', \', initials)'))->take($limit)->get();
            return new JsonResponse($authors);
        }

        if ($resource === 'organisations') {
            $organisations = Organisation::whereHas('datasets', function ($q) use ($query_string) {
                $q->published();
                if(!auth()->user()){
                    $q->openAccess();
                }
            })->where('name', 'ilike', "%{$query_string}%")->take($limit)->get();
            return new JsonResponse($organisations->unique('name')->values()->all());
        }

        if ($resource === 'file_formats') {
            $fileFormats = FileVersion::whereHas('datasets', function ($q) use ($query_string) {
                $q->published();
                if(!auth()->user()){
                    $q->openAccess();
                }
            })->where('file_format', 'ilike', "%{$query_string}%")->take($limit)->get();
            return new JsonResponse($fileFormats->unique('file_format')->values()->all());
        }


    }
}
