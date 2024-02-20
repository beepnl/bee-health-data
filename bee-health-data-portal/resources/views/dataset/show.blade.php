@extends('layouts.app')

@section('title', __('Dataset'))

@section('content')

    <x-h1>Dataset: {{$dataset->name}}</x-h1>

    <div class="mb-4">
        <span>Description:</span>
        <div>{{$dataset->description}}</div>
    </div>

    <div>
        <span>Keywords:</span>
        @foreach ($dataset->keywords as $keyword)
            <span>{{$keyword->name}}@if(!$loop->last), @endif</span>        
        @endforeach
    </div>

    <div>
        <span>{{Str::plural('Author', $dataset->authors->count())}}: </span>
        @foreach ($dataset->authors as $author)
            <span>{{$author->lastname}}, {{$author->initials}}@if(!$loop->last), @endif</span>        
        @endforeach
    </div>

    <div>Owning organisation: {{$dataset->organisation->name}}</div>

    <div>
        <span>As download available for: </span>
        @if($dataset->access_type == $dataset::ACCESS_TYPE_REGISTERED_USERS)
            All portal users
        @elseif ($dataset->access_type == $dataset::ACCESS_TYPE_OPEN_ACCESS)
            Anyone
        @else
            <span>{{$dataset->organisation->name}}@if($dataset->authorization_organisations->count()), @endif</span>
            @foreach ($dataset->authorization_organisations as $authorization_organisation)
                <span>{{$authorization_organisation->name}}@if(!$loop->last), @endif</span>
            @endforeach
        @endif
    </div>

    @if($dataset->license_id)
    <div class="mb-4">
        <span>License: </span>
        <span>{{$dataset->license->label}}</span>
    </div>
    @endif

    @if($dataset->digital_object_identifier)
    <div class="mb-4">
        <span>DOI: </span>
        <span><a target="_blank" href="https://doi.org/{{$dataset->digital_object_identifier}}">{{$dataset->digital_object_identifier}}</a></span>
    </div>
    @endif

    @if($dataset->files()->exists())
        ({{$dataset->files->count()}}) {{Str::plural('File', $dataset->files->count())}}

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Type</th>
                    <th scope="col">Size</th>
                    <th scope="col">Last modified</th>
                    <th scope="col">Version</th>
                    @if($dataset->is_downloadable)<th scope="col">Action</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($dataset->files as $file)     
                    <tr>
                        <td>{{$file->filename}}</td>
                        <td>
                            
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#modal-file-description-{{$file->id}}">
                            {{$file->short_description}}
                            </a>
                            <div class="modal fade" id="modal-file-description-{{$file->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Description</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    {{$file->description}}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                                </div>
                            </div>
                            </div>
                        
                        </td>
                        <td>{{$file->file_format}}</td>
                        <td>{{App\Helpers\File::filesize($file->size)}}</td>
                        <td>{{(new Illuminate\Support\Carbon($file->updated_at))->format('d-m-Y')}}</td>
                        <td>{{$file->version}}</td>
                        @if($dataset->is_downloadable)
                        <td>
                            <a class="btn btn-primary" href="{{route('files.show', ['file_version' => $file->id])}}">download</a>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div>

    </div>
    @if($dataset->is_downloadable)
        <div class="mb-2">
            <a class="btn btn-primary" href="{{route('files.index', ['dataset_id' => $dataset->id])}}">Download</a>
            <p>Download the complete dataset as .zip file<p>
        </div>
    @else
        <div class="mb-2">
            <form action="{{route('authorization_requests.create', ['dataset_id'=>$dataset->id])}}">
                <input type="hidden" name="dataset_id" value="{{$dataset->id}}"/>
                <button @if($dataset->is_requested) disabled @endif class="btn btn-primary" >Request access</button>
            </form>
            @if($dataset->is_requested)
                <p>Access request for this dataset is pending.</p> 
            @else
                <p>On the next screen you will be able to enter a note to accompany the request.</p> 
            @endif
        </div>
    @endif

    @if($dataset->is_owner)
        <div class="mb-2">
            <a class="btn btn-secondary" href="{{route('datasets.edit', ['dataset' => $dataset->id])}}">Edit</a>

            <!-- Button trigger modal -->
            <button type="button" class="btn btn-link text-danger" data-toggle="modal" data-target="#removeDataset">
                Remove
            </button>

            <!-- Modal -->
            <div class="modal fade" id="removeDataset" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-danger">Attention</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Are you sure that you want to remove this dataset?
                            </p>
                            <p>
                                It will completely be removed from the data portal. This action cannot be undone
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" onclick="doSubmit('delete-dataset-form')">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" id="delete-dataset-form" action="{{route('datasets.destroy', ['dataset' => $dataset->id])}}">
                @csrf
                @method('delete')
            </form>
        </div>
    @endif

@endsection

<script>
    function doSubmit(elementId){
        document.getElementById(elementId).submit();
    }
</script>
