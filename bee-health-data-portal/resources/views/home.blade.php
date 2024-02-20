@extends('layouts.app')

@section('carosel')
    @include('sites.carosel')
@endsection

@section('content')

    <x-h1>Search</x-h1>

    <form class="d-flex justify-content-center" method="GET" action="{{route('datasets.index')}}">

        <div class="input-group mb-3">
            <input name="query" id="query" type="text" class="form-control form-control-lg @error('query') is-invalid @enderror">
            @error('query')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="input-group-append">
                <button class=" btn btn-lg btn-warning text-white" type="submit">Search</button>
            </div>
        </div>
        
    </form>

    <div class="mb-4 d-flex justify-content-start">
        <a href="{{route('datasets.index')}}">
        @if(auth()->user())
            {{$initialStat['total_datasets']}}
            datasets available
        @else
            {{$initialStat['total_datasets_with_own_access']}}
            open datasets available
        @endif
        </a>
    </div>

     <div class="row mb-4">
        <div class="col-xs-12 col-lg-12">
            <h3 class="mb-4 mt-4">Dataset Statistics</h3>
            <div class="badges">
                <a href="/datasets" class="badge-group numbers" data-aos="fade-up">
                    <span id="total_datasets" class="badge circle outline bg-info text-dark count">{{$initialStat['total_datasets']}}</span>
                    <label for="total_datasets">Total number of datasets</label>
                </a>
                <a href="/datasets?owner=1" class="badge-group numbers" data-aos="fade-up">
                    <span id="total_datasets_with_own_access" class="badge circle outline bg-info text-dark count">{{$initialStat['total_datasets_with_own_access']}}</span>
                    <label for="total_datasets_with_own_access">Total datasets you have access to for download</label>
                </a>
                <div class="badge-group numbers" data-aos="fade-up">
                    <span id="total_file_size" class="badge circle outline bg-info text-dark count bytes-to-size">{{$initialStat['total_file_size']}}</span>
                    <label for="total_file_size">Total file size</label>
                </div>
                <div class="badge-group numbers" data-aos="fade-up">
                    <span id="avg_file_size" class="badge circle outline bg-info text-dark count bytes-to-size">{{$initialStat['avg_file_size']}}</span>
                    <label for="avg_file_size">Avg file size</label>
                </div>
                <div class="badge-group numbers" data-aos="fade-up">
                    <span id="max_files_in_dataset" class="badge circle outline bg-info text-dark count">{{$initialStat['max_files_in_dataset']}}</span>
                    <label for="max_files_in_dataset">Max files in dataset</label>
                </div>
                <div class="badge-group numbers" data-aos="fade-up">
                    <span id="avg_files_per_dataset" class="badge circle outline bg-info text-dark count">{{$initialStat['avg_files_per_dataset']}}</span>
                    <label for="avg_files_per_dataset">Avg files per dataset</label>
                </div>
            </div>
        </div>
     </div>
    
    <div class="row mb-4">
        <div class="col-xs-12 col-lg-6">
            <h3 class="mb-4 mt-4">Formats</h3>
            <div>
                <canvas id="formatsChart" class="chart" width="400" height="200"></canvas>
            </div>
        </div>
        <div class="col-xs-12 col-lg-6">
            <h3 class="mb-4 mt-4">Organisations</h3>
            <div>
                <canvas id="organisationStat" class="chart" width="400" height="200"></canvas>
            </div>
        </div>
     </div>
   
    <div class="row mb-4">
        <div class="col-xs-12 col-lg-12">
            <h3 class="mb-4 mt-4">Keywords</h3>
            <div>
                @foreach ($keywordStat as $keyword)
                    @if (auth()->user())
                    <a href="{{$keyword['url']}}" type="button" class="btn btn-outline-warning position-relative mr-2 mb-2 text-dark">
                        {{$keyword['name']}} ({{$keyword['total']}})
                    </a>
                    @else
                    <a tabindex="-1" aria-disabled="true" class="btn btn-outline-warning pe-none position-relative mr-2 mb-2 text-dark">
                        {{$keyword['name']}} ({{$keyword['total']}})
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
     </div>
    
    <script src="{{asset('js/chart.js')}}" ></script>
    <script>
        let formats = {!!$fileFormats!!}
        let organisations = {!!$organisationStat!!}
    </script>
    @if(auth()->user())
    <script>
        renderStatistics(formats, organisations, has_links=true)
    </script>
    @else
    <script>
        renderStatistics(formats, organisations, has_links=false)
    </script>
    @endif

@endsection
