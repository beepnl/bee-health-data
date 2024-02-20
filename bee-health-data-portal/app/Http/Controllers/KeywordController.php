<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostKeywordRequest;
use App\Models\Dataset;
use App\Models\Keyword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class KeywordController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Keyword::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keywords = Keyword::orderBy('created_at', 'desc');

        return $request->wantsJson()
            ? new JsonResponse($keywords->where('name', 'ilike', $request->input('query').'%')->take($request->input('limit', config('database.autosuggest_records')))->get())
            : view('keyword.index', ['keywords' => $keywords->paginate(config('database.paginate'))]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Keyword $keyword)
    {
        $this->authorize('create', $keyword);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostKeywordRequest $request, Keyword $keyword)
    {
        if(is_array($request->input('name.*'))){
            $name = $request->input('name.*')[0];
        }else{
            $name = $request->input('name');
        }
        $result = $keyword->firstOrCreate([
            'name' => $name
        ]);

        if($request->has('dataset_id')){
            $dataset = Dataset::findOrFail($request->dataset_id);
            $keywords = $dataset->keywords();
            $dataset->keywords()->attach($result->id, ['order'=> $keywords->max('order') + 1]);
        }
        
        return $request->wantsJson()
            ? new JsonResponse($result)
            : redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Keyword $keyword)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Keyword $keyword)
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
    public function update(PostKeywordRequest $request, Keyword $keyword)
    {
        $keyword->update([
            'name' => $request->input('name.*')[0]
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Keyword $keyword)
    {
        if($request->has('dataset_id')){
            $keyword->datasets()->detach($request->dataset_id);
            return $request->wantsJson()
            ? new JsonResponse(null, 204)
            : redirect()->back();
        }

        if(Gate::allows('isSuperAdmin')){
            $keyword->datasets()->detach();
            $keyword->delete();
        }

        return $request->wantsJson()
            ? new JsonResponse(null, 204)
            : redirect()->back();
    }
}
