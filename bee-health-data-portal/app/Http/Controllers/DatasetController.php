<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatasetRequest;
use App\Models\Author;
use App\Models\Authorization;
use App\Models\Dataset;
use App\Models\FileVersion;
use App\Models\Keyword;
use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;

class DatasetController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Dataset::class);
        // $this->authorizeResource(Dataset::class);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords_request = $request->input('keywords', []);
        $formats_request = $request->input('formats', []);
        $organisations_request = $request->input('organisations', []);
        $authors_request = $request->input('authors', []);
        $date_request = $request->input('date');
        $sort_request = $request->input('sort', 'updated_at-desc');
        $owner_request = $request->input('owner');
        list($orderColumn, $orderDest) = explode('-', $sort_request);
        $pagination_appends = [
            'keywords' => $keywords_request,
            'formats' => $formats_request,
            'organisations' => $organisations_request,
            'authors' => $authors_request,
            'date' => $date_request,
            'sort' => $sort_request,
            'owner' => $owner_request,
        ];
        $datasets = Dataset::published();
        
        // if null
        if(!$request->user()){
            $datasets = $datasets->openAccess();
        }

        $datasets
            ->where(DB::raw('concat(name, \' \', description, \' \', digital_object_identifier)'), 'ilike', '%'. $request->input('query', '').'%')
            ->whereHas('keywords', function($query) use ($keywords_request){
                if($keywords_request){
                    $query->whereIn('id', $keywords_request);
                }
            })
            ->whereHas('files', function ($query) use ($formats_request) {
                if ($formats_request) {
                    $query->whereIn('file_format', $formats_request);
                }
            })
            ->whereHas('organisation', function ($query) use ($organisations_request) {
                if ($organisations_request) {
                    $query->whereIn('id', $organisations_request);
                }
            })
            ->whereHas('authors', function ($query) use ($authors_request) {
                if ($authors_request) {
                    $query->whereIn('id', $authors_request);
                }
            });

        if($date_request){
            $datasets = $datasets->where('updated_at', '>=', Carbon::parse($date_request)->toDateTimeString() );
        }

        if(!empty($orderColumn)){
            $datasets = $datasets->orderBy($orderColumn, $orderDest === 'desc' ? 'desc' : 'asc');
        }else{
            $datasets->latest();
        }

        if(!Gate::allows('isSuperAdmin')){
            if($request->has('own_organisation')){
                $organisation_ids = Auth::user()->organisations->pluck('id');
                $datasets = $datasets->ofOrganisations($organisation_ids);
            }
        }

        if($request->has('owner') && in_array($owner_request, ['1', 'true'])){
            $records = $datasets->get()->filter(function($dataset){
                return $dataset->is_downloadable;
            });
    
            $page = Paginator::resolveCurrentPage() ?: 1;
            $perPage = config('database.paginate');
            $datasets = new LengthAwarePaginator(
                $records->forPage($page, $perPage), $records->count(), $perPage, $page, ['path' => Paginator::resolveCurrentPath()]
            );
        }else{
            $datasets = $datasets->paginate(config('database.paginate'));
        }
        $datasets = $datasets->appends($pagination_appends);
        $selectedOwner = ($request->has('owner') && in_array($owner_request, ['true', '1'])) ? true : false;

        $selectedKeywords = Keyword::whereIn('id', $keywords_request)->get()->map(function($keyword){
            return ['text'=>$keyword->name, 'value' => $keyword->id];
        })->toJson();
        
        $selectedFormats = collect($formats_request)->map(function($format){
            return ['text' => $format, 'value' => $format];
        })->toJson();

        $selectedOrganisations = Organisation::whereIn('id', $organisations_request)->get()->map(function ($organisation) {
            return ['text' => $organisation->name, 'value' => $organisation->id];
        })->toJson();

        $selectedAuthors = Author::whereIn('id', $authors_request)->get()->map(function ($author) {
            return ['text' => $author->lastname, 'value' => $author->id];
        })->toJson();
        
        $selectedDate = $date_request;
        $selectedSort = $sort_request;

        return view('dataset.index', compact('datasets', 'selectedOwner', 'selectedKeywords', 'selectedFormats', 'selectedOrganisations', 'selectedAuthors', 'selectedDate', 'selectedSort'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function show(Dataset $dataset)
    {
        if(is_null(request()->user()) && !$dataset->is_downloadable){
            return abort(401);
        }
        if($dataset->is_published){
            return view('dataset.show', compact('dataset'));
        }
        if($dataset->user->id === Auth::user()->id){
            return redirect()->route('datasets.edit', ['dataset' => $dataset->id]);
        }
        return redirect()->route('datasets.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Dataset $dataset)
    {   
        $this->authorize('create', $dataset);
        Auth::user()->datasets()->inactive()->delete();
        $dataset = $dataset->create([
            'user_id' => Auth::id(),
            'publication_state' => $dataset::PUBLICATION_STATES_INACTIVE,
            'access_type' => $dataset::ACCESS_TYPE_OWNING_ORGANISATION_ONLY
        ]);
        return redirect()->route('datasets.edit', ['dataset' => $dataset->id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\DatasetRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DatasetRequest $request)
    {
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function edit(Dataset $dataset)
    {
        $this->authorize('update', $dataset);
        $keywords = $dataset->keywords->map(function($keyword){
            return ["text"=>$keyword->name, "value"=>$keyword->id];
        })->toJson();
        $authors = $dataset->authors->toJson();
        $user = $dataset->user;
        $organisations = $user->organisations;
        if (!$organisations->count()) {
            $organisations = $dataset->organisation()->get();
        }
        $authorization_organisations = $dataset->authorization_organisations->map(function ($value) {
            return ["text" => $value->name, "value" => $value->id];
        })->toJson();
        $files = $dataset->files->toJson();
        if(Gate::allows('isSuperAdmin')){
            $organisations = Organisation::latest()->get();
        }
        return view('dataset.edit', compact(['authors', 'dataset', 'keywords', 'files', 'organisations', 'authorization_organisations']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\DatasetRequest  $request
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function update(DatasetRequest $request, Dataset $dataset)
    {
        $this->authorize('update', $dataset);
        $dataset->update([
            'name' => $request->name,
            'description' => $request->description,
            'organisation_id' => $request->organisation_id,
            'digital_object_identifier' => $request->digital_object_identifier,
            'license_id' => $request->license,
            'access_type' => $request->access_type,
            'publication_state' => $request->publication_state
        ]);

        if ($request->access_type !== Dataset::ACCESS_TYPE_BY_REQUEST) {
            $dataset->authorizations()->delete();
        }

        if($dataset->is_draft){
            return redirect()->route('my_datasets.index')->with('status', 'You saved a draft of dataset \''.$dataset->name.'\' successfully.');
        }
        return redirect()->route('datasets.show', ['dataset' => $dataset->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dataset  $dataset
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dataset $dataset)
    {
        $this->authorize('delete', $dataset);
        $dataset->keywords()->detach();
        $dataset->authors()->delete();
        $dataset->files()->delete();
        Storage::deleteDirectory("datasets/{$dataset->id}");
        $dataset->delete();
        return redirect()->route('home');
    }
}
