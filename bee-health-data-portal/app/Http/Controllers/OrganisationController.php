<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostOrganisationRequest;
use App\Models\Dataset;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganisationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Organisation::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organisations = Organisation::latest();

        if($request->wantsJson()){
            if($request->has('scope')){
                $organisations->{Str::camel($request->scope)}();
            }
            if ($request->has('dataset')) {
                $dataset = Dataset::find($request->input('dataset'));
                $organisations->exceptDatasetOrganisation($dataset);
            }
            if ($request->has('query')) {
                $organisations->ofName($request->input('query', ''));
            }
            if ($request->has('limit')) {
                $organisations->take($request->input('limit', config('database.autosuggest_records')));
            }
            return new JsonResponse($organisations->get());
        }
        $organisations = $organisations->paginate(config('database.paginate'));
        return view('organisation.index', compact('organisations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Organisation $organisation)
    {
        $this->authorize('create', $organisation);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostOrganisationRequest $request, Organisation $organisation)
    {
        $organisation->create([
            'name' => $request->input('name.*')[0],
            'is_bgood_partner' => $request->input('is_bgood_partner', false)
        ]);
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Organisation $organisation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Organisation $organisation)
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
    public function update(PostOrganisationRequest $request, Organisation $organisation)
    {
        $organisation->update([
            'name' => $request->input('name.*')[0],
            'is_bgood_partner' => $request->input('is_bgood_partner', false)
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organisation $organisation)
    {
        $this->authorize('delete', $organisation);
        $organisation->delete();
        return redirect()->back();
    }
}
