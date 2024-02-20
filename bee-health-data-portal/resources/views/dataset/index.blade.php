@extends('layouts.app')

@section('title', __('Home'))

@section('content')

    <div class="row">
        <div class="col-xs-12 col-lg-3"></div>
        <div class="col-xs-12 col-lg-9">
            <x-h1 class="mt-5">Search</x-h1>
            <form class="d-flex" method="GET" action="{{route('datasets.index')}}">
                <div class="input-group mb-3">
                    <input name="query" id="query" value="{{request('query')}}" type="text" class="form-control form-control-lg @error('query') is-invalid @enderror">
                    @error('query')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="input-group-append">
                        <button class=" btn btn-lg btn-warning text-white" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <div class="mb-4 d-flex justify-content-start">{{$datasets->total()}} results</div>
        </div>
    </div>

    <div class="row ">
        <div class="col-xs-12 col-lg-3 mb-4">
            <form method="GET" action="{{route('datasets.index')}}">
                <input type="hidden" name="query" value="{{request('query')}}">
                <div id='app-facet-search'>
                    <facet-search selected-owner="{{$selectedOwner}}" selected-keywords="{{$selectedKeywords}}" selected-formats="{{$selectedFormats}}" selected-date="{{$selectedDate}}" selected-organisations="{{$selectedOrganisations}}" selected-authors="{{$selectedAuthors}}" selected-sort="{{$selectedSort}}" />
                </div>
                <button class="btn btn-primary btn-block">Search</button>
            </form>
        </div>
        <div class="col-xs-12 col-lg-9">
            


            @foreach ($datasets as $dataset)
                <div class="row mb-4">
                    <div class="col">
                        <h2 class="mb-2"><a href="{{route('datasets.show', ['dataset'=>$dataset->id])}}">{{$dataset->name}}</a></h2>
                        <div>Files: {{$dataset->number_files}} ({{$dataset->unique_extensions->implode(', ')}})</div>
                        <div>Last modified: {{$dataset->lastModified}}</div>
                        <div>Keywords: {{$dataset->keywords->pluck('name')->implode(', ')}}</div>
                        <div>Owning organisation (authors): {{$dataset->organisation->name}}
                            <span>(</span>@foreach ($dataset->authors as $author)<span>{{$author->lastname}}, {{$author->initials}}</span><span>@if ($loop->remaining), @endif</span>@endforeach<span>)</span></div>
                        <div>Description: {{$dataset->short_description}}</div>
                        @if($dataset->license)
                            <div>License: {{$dataset->license->label}}</div>
                        @endif
                        @if($dataset->digital_object_identifier)
                            <div>Doi: <a target="_blank" href="https://doi.org/{{$dataset->digital_object_identifier}}">{{$dataset->digital_object_identifier}}</a></div>
                        @endif
                    </div>
                </div>
                @endforeach
                {{ $datasets->links('sites.pagination') }}
        </div>
    </div>
    
@endsection

