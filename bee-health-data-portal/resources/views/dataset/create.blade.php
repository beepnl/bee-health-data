@extends('layouts.app')

@section('title', __('Manage users'))

@section('content')
    <x-h1>New dataset</x-h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{route('datasets.store')}}" id="datasets_store">
        @csrf

        {{-- Files here --}}

        <div class="form-group">
            <label for="name">Dataset name*</label>
            <input name="name" id="name" type="text" maxlength="140" required class="form-control @error('name') is-invalid @enderror" value="{{old('name')}}">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div id="app-keywords">
            <keywords-component invalidfeedback="@error('keywords.*') {{ $message }} @enderror"></keywords-component>
        </div>
        
        <div class="form-group">
            <label for="organisation">Owning {{Str::plural('organisation')}}</label>
            <select name="organisation_id" id="organisation" class="custom-select @error('organisation_id') is-invalid @enderror" multiple aria-describedby="organisationHelpBlock">
                <option selected value="">Select organisation</option>
                <option value="1" >One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
            @error('organisation_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small id="organisationHelpBlock" class="form-text text-muted">
                The organisation you are member of
            </small>
        </div>

        {{-- Authors here --}}
        <div id="app-authors">
            <authors-component selected-json-stringify-authors="{{old('authors', $authors )}}"></authors-component>
        </div>

        <div class="form-group">
            <label for="description">Description*</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="5" maxlength="500" required>{{old('description')}}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Digital object identifier</label>
            <input name="digital_object_identifier" type="text" class="form-control @error('digital_object_identifier') is-invalid @enderror" aria-describedby="digital_object_identifierHelpBlock" value="{{old('digital_object_identifier')}}">
            <small id="digital_object_identifierHelpBlock">Type or paste a DOI name, e.g., 10.1000/xyz123, into the text box above. (Be sure to enter all of the characters before and after the slash. Do not include extra characters, or sentence punctuation marks.)</small>
            @error('digital_object_identifier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="access_type">Permission to access</label>
            @error('access_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted mb-2">
                Portal users from your organisation can download your published datasets. You can define here which additional organisations have download access.
                Your dataset and its metadata is visible for all portal users and they can request permission to download it. The administer of your organisations manages these requests.
            </small>
            <div class="form-check">
                <input class="form-check-input @error('access_type') is-invalid @enderror" name="access_type" type="radio" value="" id="defaultCheck1" aria-describedby="access_typeHelpBlock">
                <label class="form-check-label" for="defaultCheck1">
                    Specific organisations
                </label>
                <small id="access_typeHelpBlock" class="form-text text-muted">
                    <p>Users for the organisations you select are able to download this dataset.</p>
                    <a href="#" class="text-primary">Click here to add all B-GOOD partners</a>
                </small>
                <div class="form-group">
                    <select name="authorization_organisations" id="authorization_organisations" class="custom-select @error('authorization_organisations') is-invalid @enderror" multiple aria-describedby="authorization_organisationsHelpBlock">
                        <option selected>Select organisation</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                    @error('authorization_organisations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form-check">
                <input class="form-check-input @error('access_type') is-invalid @enderror" name="access_type" type="radio" value="" id="defaultCheck2" aria-describedby="access_typeHelpBlock">
                <label class="form-check-label" for="defaultCheck2">
                    All portal users
                </label>
                <small id="access_typeHelpBlock" class="form-text text-muted">
                    All users of the B-GOOD portal (e.g. invited non-B-GOOD organisations) are able to download this dataset
                </small>
            </div>
        </div>

        <input type="hidden" name="publication_state">
        @error('publication_state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="text-center">
            <input type="button" class="btn btn-primary" onclick="DoSubmit('datasets_store', 'draft')" value="Save draft">
            <input type="button" class="btn btn-primary" onclick="DoSubmit('datasets_store', 'published')" value="Publish">
        </div>

    </form>

@endsection

<script>
    
    function DoSubmit(form_id, state){
        var form = document.getElementById(form_id);
        form.publication_state.value = state;
        form.submit();
    }

</script>
