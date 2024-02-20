@extends('layouts.app')

@section('title', __('New dataset'))

@section('content')
    <x-h1>{{$dataset->is_edit ? 'Edit' : 'New'}} dataset</x-h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" novalidate id="datasets_delete" action="{{route('datasets.destroy', $dataset)}}">
        @csrf
        @method('DELETE')
    </form>
    <form method="POST" novalidate action="{{route('datasets.update', ['dataset' => $dataset->id])}}" id="datasets_update">
        @csrf
        @method('PUT')
        {{-- Files here --}}
        @error('files') {{$message}} @enderror
        <div id="app-files" class="mb-4">
            <files-component available-file-formats="" selected-json-stringify-files="{{old('files', $files )}}" dataset-id="{{$dataset->id}}" invalidfeedback="@error('files') {{ $message }} @enderror" />
        </div>

        <div class="form-group">
            <label for="name">Dataset name*</label>
            <input name="name" id="name" type="text" maxlength="140" required class="form-control @error('name') is-invalid @enderror" value="{{old('name', $dataset->name)}}">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div id="app-keywords">
            <keywords-component selected-json-stringify-keywords="{{old('keywords', $keywords)}}" dataset-id="{{$dataset->id}}" invalidfeedback="@error('keywords') {{ $message }} @enderror" />
        </div>
        
        <div class="form-group">
            <label for="organisation">Owning {{Str::plural('organisation')}}</label>
            
            @if($organisations->count() > 1)
            <select name="organisation_id" id="organisation" class="custom-select @error('organisation_id') is-invalid @enderror" aria-describedby="organisationHelpBlock">
                @foreach($organisations as $organisation)
                    <option @if($organisation->id === old('organisation_id', $dataset->organisation_id)) selected @endif value="{{$organisation->id}}">{{$organisation->name}}</option>
                @endforeach
            </select>
            @else
                <p>{{$organisations->first()->name}}</p>
                <input type="hidden" name="organisation_id" value="{{$organisations->first()->id}}" aria-describedby="organisationHelpBlock" />
            @endif
            @error('organisation_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small id="organisationHelpBlock" class="form-text text-muted">
                The organisation you are member of
            </small>
        </div>

        {{-- Authors here --}}
        <div id="app-authors">
            <authors-component selected-json-stringify-authors="{{old('authors', $authors )}}" dataset-id="{{$dataset->id}}" invalidfeedback="@error('authors') {{ $message }} @enderror" ></authors-component>
        </div>

        <div class="form-group">
            <label for="description">Description*</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="5" maxlength="500" required>{{old('description', $dataset->description)}}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Digital object identifier</label>
            <input name="digital_object_identifier" type="text" class="form-control @error('digital_object_identifier') is-invalid @enderror" aria-describedby="digital_object_identifierHelpBlock" value="{{old('digital_object_identifier', $dataset->digital_object_identifier)}}">
            <small id="digital_object_identifierHelpBlock">Type or paste a DOI name, e.g., 10.1000/xyz123, into the text box above. (Be sure to enter all of the characters before and after the slash. Do not include extra characters, or sentence punctuation marks.)</small>
            @error('digital_object_identifier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            {{-- value="{{old('digital_object_identifier', $dataset->digital_object_identifier)}}" --}}
            <label for="license">License*</label>
            <select name="license" id="license" class="custom-select @error('license') is-invalid @enderror" aria-describedby="licenseHelpBlock">
                <option selected value="">Select one of licenses</option>
                @foreach(App\Models\License::active()->order()->get() as $license)
                    @if ($license->id == $dataset->license_id)
                        <option value="{{$license->id}}" selected>{{$license->label}}</option>
                    @else
                        <option value="{{$license->id}}">{{$license->label}}</option>
                    @endif
                @endforeach
            </select>
            <small id="licenseHelpBlock">For more details please check <a href="https://creativecommons.org/licenses/" target="_blank" rel="license">https://creativecommons.org/licenses/</a></small>

            @error('license')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="access_type" class="h3 mb-4">Permission to access</label>
            @error('access_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted mb-2">
                Portal users from your organisation can download your published datasets. You can define here which additional organisations have download access.
                Your dataset and its metadata is visible for all portal users and they can request permission to download it. The administer of your organisations manages these requests.
            </small>

            <div class="form-check mb-3">
                <input @if(old('access_type', $dataset->access_type) === $dataset->allowAccessTypes['ORGANISATION_ONLY']) checked @endif class="form-check-input @error('access_type') is-invalid @enderror" name="access_type" type="radio" value="{{$dataset->allowAccessTypes['ORGANISATION_ONLY']}}" id="defaultCheck3" aria-describedby="access_typeHelpBlock">
                <label class="form-check-label" for="defaultCheck3">
                    My organisation
                </label>
            </div>

            <div class="form-check">
                <input @if(old('access_type', $dataset->access_type) === $dataset->allowAccessTypes['BY_REQUEST']) checked @endif class="form-check-input @error('access_type') is-invalid @enderror" name="access_type" type="radio" value="{{$dataset->allowAccessTypes['BY_REQUEST']}}" id="defaultCheck1" aria-describedby="access_typeHelpBlock">
                <label class="form-check-label" for="defaultCheck1">
                    Additional organisations
                </label>
                <div id="app-authorization-organisations">
                    <authorization-organisations-component selected-json-stringify-organisations="{{old('authorization_organisations', $authorization_organisations )}}" dataset-id="{{$dataset->id}}" invalidfeedback="@error('authorization_organisations') {{ $message }} @enderror" />
                </div>
            </div>
            
            <div class="form-check">
                <input @if(old('access_type', $dataset->access_type) === $dataset->allowAccessTypes['REGISTERED_USERS']) checked @endif class="form-check-input @error('access_type') is-invalid @enderror" name="access_type" type="radio" value="{{$dataset->allowAccessTypes['REGISTERED_USERS']}}" id="defaultCheck2" aria-describedby="access_typeHelpBlock">
                <label class="form-check-label" for="defaultCheck2">
                    All portal users
                </label>
                <small id="access_typeHelpBlock" class="form-text text-muted">
                    All registered users of the B-GOOD portal (e.g. invited non-B-GOOD organisations) are able to download this dataset
                </small>
            </div>

            <div class="form-check">
                <input @if(old('access_type', $dataset->access_type) === $dataset->allowAccessTypes['OPEN_ACCESS']) checked @endif class="form-check-input @error('access_type') is-invalid @enderror" name="access_type" type="radio" value="{{$dataset->allowAccessTypes['OPEN_ACCESS']}}" id="defaultCheck4" aria-describedby="access_typeHelpBlock">
                <label class="form-check-label" for="defaultCheck4">
                    Open access
                </label>
                <small id="access_typeHelpBlock" class="form-text text-muted">
                    All users, registered and unregistered, are able to download this dataset
                </small>
            </div>
        </div>

        <div class="form-check">
            <input id="agreement" name="agreement" type="checkbox" class="form-check-input @error('agreement') is-invalid @enderror">
            <label for="agreement" class="form-check-label">I confirm that either this dataset does not contain any personal data (i.e. by which an individual can be identified, directly or indirectly) or if it does, written consent is given by the person(s).</label>
            @error('agreement')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <input type="hidden" name="publication_state">
        @error('publication_state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="d-flex justify-content-center text-center">

            <!-- Button trigger modal -->
            <button onclick="event.preventDefault()" class="mr-2 btn btn-danger btn-lg" data-toggle="modal" data-target="#delete">
                Remove
            </button>

            <!-- Modal -->
            <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
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
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                            <input type="submit" class="btn btn-danger" onclick="DoSubmit(event, 'datasets_delete')" value="Remove">
                            
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="mr-2 btn btn-primary btn-lg" onclick="DoSubmit(event, 'datasets_update', 'draft')">Save draft</button>
            <button type="submit" class="btn btn-primary btn-lg" onclick="DoSubmit(event, 'datasets_update', 'published')">Publish</button>
        </div>

    </form>

@endsection

<script>
   
    function DoSubmit(event, form_id, state){
        event.preventDefault()
        var form = document.getElementById(form_id);
        if(state != undefined){
            form.publication_state.value = state;
        }
        form.submit();
    }

</script>
