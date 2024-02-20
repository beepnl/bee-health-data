@extends('layouts.app')

@section('title', __('Manage access requests'))

@section('content')
    <x-h1>Manage access requests</x-h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h2>Dataset: {{$authorization_request->requesting_dataset->name}}</h2>

    <p class="font-bold">Access request</p>
    <p>Reference: {{$authorization_request->reference}}</p>
    <p>Organisation: -</p>
    <p>User: {{$authorization_request->requesting_user->fullname}}</p>
    <p>Date requested: {{$authorization_request->requested_at}}</p>
    <p>Request note: {{$authorization_request->notes}}</p>

    <form id="authorization_requests_update" class="mt-8" method="POST" action="{{route('authorization_requests.update', ['authorization_request' => $authorization_request->id])}}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Note with response</label>
            <textarea class="form-control @error('response_note') is-invalid @enderror" name="response_note" rows="5" >{{$authorization_request->response_note}}</textarea>
            @error('response_note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <input type="hidden" name="is_approved"/>

        @if($authorization_request->approved_at || $authorization_request->rejected_at)
            <div>Status: {{$authorization_request->is_approved ? 'approved' : 'rejected'}}</div>
        @endif
        <div class="text-center">
            <input type="button" @if($authorization_request->is_approved) disabled @endif class="btn btn-primary" onclick="DoSubmit('authorization_requests_update', '1')" value="Approve">
            <input type="button" class="btn btn-primary" onclick="DoSubmit('authorization_requests_update', '0')" value="Reject">
        </div>
    </form>
@endsection

<script>
    
    function DoSubmit(form_id, is_approved){
        var form = document.getElementById(form_id);
        form.is_approved.value = is_approved;
        form.submit();
    }

</script>
