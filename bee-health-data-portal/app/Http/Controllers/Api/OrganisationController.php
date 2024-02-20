<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function dropdown(Request $request)
    {
        $organisations = Organisation::ofName($request->q)->get()->map(function($organisation){
            return [
                'id' => $organisation->id,
                'value' => $organisation->name,
                'key' => $organisation->id
            ];
        });
        return response()->json($organisations->toArray());
    }
}
