@extends('layouts.app')

@section('title', 'Login')

@section('content')

    <x-h1>Manage Keywords</x-h1>

    @if($keywords->count())
        <div class="mb-4 d-flex justify-content-center">{{$keywords->total()}} results</div>
    @endif

    @foreach ($keywords as $keyword)
        @can('view', $keyword)

            <div class="card mb-4">
                <div class="card-body">

                    <div>
                        @can('update', $keyword)
                            <form class="" id="keywords-update-form-{{$keyword->id}}" method="POST" action="{{ route('keywords.update', $keyword->id) }}">
                                @method('put')
                                @csrf
                                <div class="form-group mb-2">
                                    <div>
                                        <label>{{__('Keywords')}}</label>
                                        <input type="text" class="form-control @error('name.'.$keyword->id) is-invalid @enderror" name="name[{{$keyword->id}}]" value="{{ $keyword->name }}" minlength="1" maxlength="25">
                                        @error('name.'.$keyword->id)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                            </form>
                        @endcan
                    </div>

                    <div class="card-text text-right">
                        @can('update', $keyword)
                        <button onclick="onSubmit('keywords-update-form-{{$keyword->id}}')" class="mb-2 mx-2 btn btn-primary" type="submit">Save</button>
                        @endcan
                        @can('delete', $keyword)

                            <!-- Button trigger modal -->
                            <button type="button" class="mb-2 btn btn-danger" data-toggle="modal" data-target="#removeKeyword-{{$keyword->id}}">
                                Remove @if($keyword->datasets->count()) ({{$keyword->datasets->count()}}) @endif
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="removeKeyword-{{$keyword->id}}" tabindex="-1" role="dialog" aria-hidden="true">
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
                                                Are you sure that you want to remove this keyword?
                                            </p>
                                            <p>
                                                It will completely be removed from the data portal. This action cannot be undone
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                            <button type="button" class="btn btn-danger" onclick="onSubmit('keywords-delete-form-{{$keyword->id}}')">Yes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="keywords-delete-form-{{$keyword->id}}" method="POST" action="{{ route('keywords.destroy', $keyword->id) }}">
                                @method('delete')
                                @csrf
                            </form>
                        @endcan
                    </div>

                </div>
            </div>
        @endcan
    @endforeach

    <div class="card card-transparent mb-4">
        <div class="card-header">
            New keyword
        </div>
        <div class="card-body">
            <div class="d-flex">
                <div style="flex-basis: 100%;">
                    @can('create', \App\Models\Keyword::class)
                        <form class="" id="keywords-create-form" method="POST" action="{{ route('keywords.store') }}">
                            @csrf
                            <div class="form-group mb-2">
                                <div>
                                    <input type="text" class="form-control @error('name.0') is-invalid @enderror" name="name[0]" value="{{old('name.0')}}" minlength="1" maxlength="25">
                                    @error('name.0')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                        </form>
                    @endcan
                </div>
                <div class="">
                    <button onclick="onSubmit('keywords-create-form')" class="mb-2 mx-2 btn btn-primary" type="submit">Save</button>
                </div>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-center">
        {{ $keywords->links('sites.pagination') }}
    </div>

@endsection

<script>
    function onSubmit(elementId){
        document.getElementById(elementId).submit();
    }
</script>
