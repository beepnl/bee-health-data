<?php

namespace App\Http\Controllers;

use App\Models\TermsAndConditions;
use Illuminate\Http\Request;

class TermsAndConditionsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('isSuperAdmin');
        TermsAndConditions::create($request->all());
        return redirect()->route('terms-and-conditions.show');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $termsAndConditions = (new TermsAndConditions)->latest()->firstOrFail();
        return view('terms_and_conditions.index', ['content' => $termsAndConditions->content]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $this->authorize('isSuperAdmin');
        $termsAndConditions = (new TermsAndConditions)->latest()->firstOrFail();
        return view('terms_and_conditions.edit', ['content' => $termsAndConditions->content]);
    }

}
