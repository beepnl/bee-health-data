@extends('layouts.app')

@section('title', __('Manage Organisation'))

@section('content')

    <x-h1>Manage Organisation</x-h1>

    @if($organisations->count())
        <div class="mb-4 d-flex justify-content-center">{{$organisations->total()}} results</div>
    @endif

    @foreach ($organisations as $organisation)
    <div class="card mb-4">
        <div class="card-body">
            
            <div>
                @can('update', $organisation)
                    <form class="" id="organisation-update-form-{{$organisation->id}}" method="POST" action="{{ route('organisations.update', $organisation->id) }}">
                        @method('put')
                        @csrf
                        <div class="form-group mb-2">
                            <div>
                                <label>{{__('Organisation')}}</label>
                                <input type="text" class="form-control @error('name.'.$organisation->id) is-invalid @enderror" name="name[{{$organisation->id}}]" value="{{ $organisation->name }}" minlength="1" maxlength="25">
                                @error('name.'.$organisation->id)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-check">
                            <input name="is_bgood_partner" @if ($organisation->is_bgood_partner) checked @endif class="form-check-input" type="checkbox" id="is_bgood_partner_{{$organisation->id}}" value="true">
                            <label class="form-check-label" for="is_bgood_partner_{{$organisation->id}}">
                                    {{__('B-GOOD partner')}}
                            </label>
                        </div>
                    </form>
                @endcan               
            </div>
            
            <div class="card-text text-right">
                @can('update', $organisation)
                <button onclick="onSubmit('organisation-update-form-{{$organisation->id}}')" class="mb-2 mx-2 btn btn-primary" type="submit">Save</button>
                @endcan
                @can('delete', $organisation)
                
                    <!-- Button trigger modal -->
                    <button type="button" class="mb-2  btn btn-danger" data-toggle="modal" data-target="#removeOrganisation-{{$organisation->id}}">
                        Remove @if($organisation->datasets->count()) ({{$organisation->datasets->count()}}) @endif
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="removeOrganisation-{{$organisation->id}}" tabindex="-1" role="dialog" aria-hidden="true">
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
                                        Are you sure that you want to remove this organisation?
                                    </p>
                                    <p>
                                        It will completely be removed from the data portal. This action cannot be undone
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                    <button type="button" class="btn btn-danger" onclick="onSubmit('organisation-delete-form-{{$organisation->id}}')">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form id="organisation-delete-form-{{$organisation->id}}" method="POST" action="{{ route('organisations.destroy', $organisation->id) }}">
                        @method('delete')
                        @csrf
                    </form>
                @endcan
            </div>

        </div>
    </div>
    @endforeach

    <div class="card card-transparent mb-4">
        <div class="card-header">
            New organisation
        </div>
        <div class="card-body">
            <div class="d-flex">
                <div style="flex-basis: 100%;">
                    @can('create', \App\Models\Organisation::class)
                        <form id="organisation-create-form" method="POST" action="{{ route('organisations.store') }}">
                            @csrf
                            <div class="form-group mb-2">
                                <div>
                                    <input placeholder="{{__('Organisation')}}" type="text" class="form-control @error('name.0') is-invalid @enderror" name="name[0]" value="{{old('name.0')}}" minlength="1" maxlength="25">
                                    @error('name.0')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-check">
                                <input name="is_bgood_partner" class="form-check-input" type="checkbox" id="is_bgood_partner" value="true">
                                <label class="form-check-label" for="is_bgood_partner">
                                    {{__('B-GOOD partner')}}
                                </label>
                            </div>
                            
                        </form>
                    @endcan
                </div>
                <div class="">
                        <button onclick="onSubmit('organisation-create-form')" class="mb-2 mx-2 btn btn-primary" type="submit">Save</button>
                </div>
            </div>

        </div>
    </div>


    <div class="d-flex justify-content-center">
      {{ $organisations->links('sites.pagination') }}
    </div>

@endsection


<script>
    function onSubmit(elementId){
        document.getElementById(elementId).submit();
    }
</script>

