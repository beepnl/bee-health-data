@extends('layouts.app')

@section('title', __('Manage users'))

@section('content')
    <x-h1>Manage users</x-h1>

    
    @error('user_id')
        {{$message}}
    @enderror

    @if($users->count())
        <div class="mb-4 d-flex justify-content-center">{{$users->total()}} results</div>
    @endif
    
    <div class="d-flex justify-content-center py-4">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status" id="status-all" value="">
            <label class="form-check-label" for="status-all">all</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status" id="status-active" value="active">
            <label class="form-check-label" for="status-active">active</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status" id="status-invitation-sent" value="invitation sent">
            <label class="form-check-label" for="status-invitation-sent">invitation sent</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="status" id="status-awaiting-activation" value="awaiting activation">
            <label class="form-check-label" for="status-awaiting-activation">awaiting activation</label>
        </div>
    </div>

    <script>
        var statusNodeList = document.querySelectorAll('input[name="status"]');
        const urlParams = new URLSearchParams(window.location.search);
        for(var i=0; i<statusNodeList.length; i++){
            statusNodeList[i].checked = false;
            if(statusNodeList[i].value == "{{ Request::get('status') }}"){
                statusNodeList[i].checked = true
            }
            statusNodeList[i].addEventListener('change', function(){
                if(this.value == ''){
                    urlParams.delete('status');
                }else{
                    urlParams.set('status', this.value);
                }
                window.location.search = urlParams;
            })
        }
    </script>

    <table class="table">
        <thead>
            <tr>
            <th scope="col">
                @sortablelink('email', 'Email')
                {{-- <i class="fas fa-sort-amount-up-alt"></i> --}}
            </th>
            <th scope="col">@sortablelink('fullname', 'Names')</th>
            <th scope="col">@sortablelink('user_role', 'Role')</th>
            <th scope="col">@sortablelink('organisation_name', 'Organisation')</th>
            <th scope="col">@sortablelink('status', 'Account status')</th>
            <th scope="col" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($users as $user)
                <tr>
                    <td>{{$user->email}}</td>
                    <td>{{$user->fullname}}</td>
                    <td>{{$user->user_role}}</td>
                    <td>{{$user->organisation_name}}</td>
                    <td>{{$user->status}}</td>
                    <td>
                        <div class="d-flex justify-content-center">
                        
                            <form class="m-0 pr-2" method="GET" action=@if($user->status == 'invitation sent'){{route('registration_invitations.edit', $user->id)}}@else{{route('members.edit', $user->id)}}@endif>
                                <input type="submit" class="btn btn-secondary" value="@lang('edit')">
                                <input type="hidden" name="organisation_id" value="{{$user->organisation_id}}">
                            </form>

                            <!-- Button trigger modal -->
                             @if($user->status == 'invitation sent')
                            <button type="button" class=" btn btn-warning mr-2" onclick="onSubmit('form-update-user-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}')">
                                @lang('resend')
                            </button>
                            @endif
                            <button type="button" class=" btn btn-danger" data-toggle="modal" data-target="#removeUser-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}">
                                @lang('delete')
                            </button>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="removeUser-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}" tabindex="-1" role="dialog" aria-hidden="true">
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
                                            Are you sure that you want to remove this user?
                                        </p>
                                        <p>
                                            It will be removed completely from the data portal. This action cannot be undone!
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                        <button type="button" class="btn btn-danger" onclick="onSubmit('form-delete-user-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}')">Yes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            @if($user->status == 'invitation sent')
                                <form class="inline-block" method="POST" id="form-delete-user-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}" action={{route('registration_invitations.destroy', $user->id)}}>
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="organisation_id" value="{{$user->organisation_id}}">
                                    <input type="hidden" name="user_role_id" value="{{$user->user_role_id}}">
                                </form>
                                <form class="inline-block" method="POST" id="form-update-user-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}" action={{route('registration_invitations.update', $user->id)}}>
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="organisation_id" value="{{$user->organisation_id}}">
                                    <input type="hidden" name="resend" value="1">
                                </form>
                            @else
                                <form class="inline-block" method="POST" id="form-delete-user-{{md5($user->id.$user->organisation_id.$user->user_role_id)}}" action={{route('members.destroy', $user->id)}}>
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="organisation_id" value="{{$user->organisation_id}}">
                                    <input type="hidden" name="user_role_id" value="{{$user->user_role_id}}">
                                </form>
                            @endif
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $users->links('sites.pagination') }}
    </div>

    @can('isAdmin')
        <div class="text-center">
            <a href="{{route('members.create')}}" class="text-center btn btn-primary">@lang('Add user')</a>
        </div>
    @endcan

@endsection

<script>
    function onSubmit(elementId){
        document.getElementById(elementId).submit();
    }
</script
