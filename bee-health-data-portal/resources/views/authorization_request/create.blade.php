@extends('layouts.app')

@section('title', __('Request access'))

@section('content')
    <x-h1>Request access</x-h1>

    <p class="text-center mb-4">A request is sent to the administrator of the organisation owning the dataset.<br/>
    You will receive an email when the request is approved or rejected.</p>

    <h2>Dataset: {{$dataset->name}}</h2>
    <form method="POST" action="{{route('authorization_requests.store')}}">
        @csrf
        <input type="hidden" name="requesting_dataset_id" value="{{$dataset->id}}" />
        <div class="form-group">
            <label>Reference</label>
            <input type="text" required class="form-control @error('reference') is-invalid @enderror" name="reference" aria-describedby="referenceHelpBlock" >
            <small id="referenceHelpBlock">Intention to publish record number. For information, see the B-GOOD 'Publication and data sharing policy'</small>

            @error('reference')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="comment">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" id="notes" rows="5" maxlength="500"></textarea>
            <small id="NotesHelpBlock">Please explain the reason for requesting access.</small>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="text-center">
            <input type="submit" class="btn btn-primary" value="Send request">
        </div>

    </form>
  

@endsection
