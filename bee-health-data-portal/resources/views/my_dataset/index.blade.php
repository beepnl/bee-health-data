@extends('layouts.app')

@section('title', 'My datasets')

@section('content')

    <x-h1>My datasets</x-h1>
    <p class="text-center">List of datasets I have drafted or published for my organisation on this portal</p>
    
    <div class="d-flex justify-content-end">
        <a class="btn btn-primary btn-lg" href="{{route('datasets.create')}}">Create new dataset</a>
    </div>

    @if (!$datasets->count())
        <div class="text-center">empty</div>
    @else
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th scope="col">Dataset</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datasets as $dataset)
                <tr>
                    <th><a href="{{route('datasets.show', ['dataset' => $dataset->id])}}">{{$dataset->name ?: 'untitled'}}</a></th>
                    <td>
                        @if ($dataset->is_draft)
                            {{$dataset::PUBLICATION_STATES_DRAFT}}
                        @endif
                        @if ($dataset->is_published)
                            {{$dataset::PUBLICATION_STATES_PUBLISHED}}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection
