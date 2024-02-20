@extends('layouts.app')

@section('title', 'My access requests')

@section('content')

    <x-h1>My access requests</x-h1>
    
    @if (!$my_access_requests->count())
        <div class="row">
            <div class="col text-center">empty</div>
        </div>
    @else
        <div class="row mb-4">
            <div class="col">Dataset</div>
            <div class="col">Status</div>
            <div class="col">Actions</div>
        </div>
    @endif

    @foreach ($my_access_requests as $my_access_request)
        <div class="row">
            <div class="col"><a target="_blank" href="{{route('datasets.show', ['dataset' => $my_access_request->requesting_dataset->id])}}">{{$my_access_request->requesting_dataset->name}}</a></div>
            <div class="col">{{$my_access_request->is_rejected ? 'rejected' : ($my_access_request->is_approved ? 'approved' : 'pending') }}</div>
            <div class="col">

                <!-- Button trigger modal -->
                <button type="button" class="mb-2  btn btn-danger" data-toggle="modal" data-target="#removeMyAccessRequest-{{$my_access_request->id}}">
                    Withdraw
                </button>

                <!-- Modal -->
                <div class="modal fade" id="removeMyAccessRequest-{{$my_access_request->id}}" tabindex="-1" role="dialog" aria-hidden="true">
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
                                    Are you sure that you want to withdraw?
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                <button type="button" class="btn btn-danger" onclick="onSubmit('my-access-request-delete-form-{{$my_access_request->id}}')">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form method="POST" id="my-access-request-delete-form-{{$my_access_request->id}}" action="{{route('my_access_requests.destroy', ['authorization_request'=>$my_access_request->id])}}">
                    @csrf
                    @method('DELETE')
                </form>
                <form method="POST" action="{{route('my_access_requests.update', ['authorization_request'=>$my_access_request->id])}}">
                    @csrf
                    @method('PUT')
                    <button {{$my_access_request->is_approved ? 'disabled' : ''}} class="btn btn-primary">Resend</button>
                </form>
            </div>
        </div>
    @endforeach

@endsection

<script>
    function onSubmit(elementId){
        document.getElementById(elementId).submit();
    }
</script>
