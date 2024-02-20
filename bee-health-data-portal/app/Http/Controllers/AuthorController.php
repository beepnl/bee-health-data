<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorRequest;
use App\Models\Author;
use App\Models\Dataset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\VarDumper\Cloner\Data;

class AuthorController extends Controller
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
    public function store(AuthorRequest $request, Author $author)
    {
        $nextOrder = 1;
        $record = $author->create([
            'lastname' => $request->lastname,
            'initials' => $request->initials,
            'organisation' => $request->organisation,
        ]);

        if($request->has('dataset_id')){
            $dataset = Dataset::findOrFail($request->dataset_id);
            $nextOrder = $dataset->authors()->max('order') + 1;
            $record->datasets()->attach($request->dataset_id, [ 'order' => $nextOrder ]);
        }

        return $request->wantsJson()
            ? new JsonResponse(['id'=> $record->id, 'order' => $nextOrder ], 201)
            : redirect(null, 201)->back();
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
    public function update(AuthorRequest $request, Author $author)
    {
        if ($request->has('order')) {
            $dataset = Dataset::findOrFail($request->dataset_id);
            $authors = $dataset->authors();
            $currentAuthor = $dataset->authors()->findOrFail($author->id);
            $targetAuthor = $dataset->authors()->wherePivot('order', $request->order)->first();
            
            $authors->updateExistingPivot($author->id, ['order' => $request->order]);
            $authors->updateExistingPivot($targetAuthor->id, ['order' => $currentAuthor->order]);

            return $request->wantsJson()
                ? new JsonResponse(['reordered' => true], 200)
                : redirect(null, 200)->back();
        }

        $author = $author->update([
            'lastname' => $request->lastname,
            'initials' => $request->initials,
            'organisation' => $request->organisation
        ]);

        // if($request->has('order')){
        //     Dataset::findOrFail($request->dataset_id)->authors()->updateExistingPivot($author, ['order' => $request->order]);
        // }

        return $request->wantsJson()
            ? new JsonResponse(null, 200)
            : redirect(null, 200)->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuthorRequest $request, Author $author)
    {
        Dataset::find($request->dataset_id)->authors()->detach($author->id);
        $author->delete();
        
        return $request->wantsJson()
            ? new JsonResponse(null, 204)
            : redirect()->back();
    }
}
