@extends('layouts.app')

@section('title', __('Access requests'))

@section('content')

    <x-h1>Access requests</x-h1>
    <p class="text-center mb-10">Access requests for datasets owned by my organisation</p>

    @if($authorization_requests->count())
        <div class="mb-4 d-flex justify-content-center">{{$authorization_requests->total()}} results</div>
    @endif
    
    <table class="table table-borderless">
        <thead>
            <tr>
                <th scope="col">@sortablelink('dataset_name', 'Dataset')</th>
                <th scope="col">@sortablelink('fullname', 'Request by')</th>
                <th scope="col">@sortablelink('user_organisation', 'organisation')</th>
                <th scope="col">@sortablelink('requested_at', 'Data requested')</th>
                <th scope="col">@sortablelink('organisation_request', 'organisation')</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($authorization_requests as $authorization_request)
                <tr>
                    <td>{{$authorization_request->dataset_name}}</td>
                    <td>{{$authorization_request->fullname}}</td>
                    <td>
                        {{$authorization_request->user_organisation}}
                    </td>
                    <td>{{$authorization_request->requested_at}}</td>
                    <td>{{$authorization_request->organisation_request}}</td>
                    <td>
                        @if(App\Models\AuthorizationRequest::find($authorization_request->id)->is_pending)
                            pending
                        @elseif(App\Models\AuthorizationRequest::find($authorization_request->id)->is_approved)
                            approved
                        @else
                            rejected
                        @endif
                    </td>
                    <td>
                        <a class="btn btn-primary btn-lg" href="{{route('authorization_requests.edit', ['authorization_request' => $authorization_request->id])}}">Open request</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!$authorization_requests->count())
        <div class="text-center">no access requests currently pending for your organisation</div>
    @endif

    <div class="d-flex justify-content-center">
        {{ $authorization_requests->links('sites.pagination') }}
    </div>

@endsection
