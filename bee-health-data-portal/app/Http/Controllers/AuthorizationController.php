<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorizationRequest;
use App\Models\Dataset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthorizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(AuthorizationRequest $request)
    {
        $dataset = Dataset::find($request->dataset_id);
        if($request->has('organisation_id')){
            $dataset->authorization_organisations()->syncWithoutDetaching([$request->organisation_id]);
        }else{
            $dataset->authorization_users()->syncWithoutDetaching([Auth::id()]);
        }

        return new JsonResponse(null, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(AuthorizationRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuthorizationRequest $request)
    {
        $dataset = Dataset::find($request->dataset_id);
        if ($request->has('organisation_id')) {
            $dataset->authorization_organisations()->detach($request->organisation_id);
        } else if ($request->has('user_id')) {
            $dataset->authorization_users()->detach($request->user_id);
        }
        return new JsonResponse(null, 204);
    }
}
