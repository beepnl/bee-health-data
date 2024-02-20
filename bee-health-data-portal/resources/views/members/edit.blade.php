@extends('layouts.app')

@section('title', __('Manage users'))

@section('content')
    <x-h1>Edit user</x-h1>

    <form method="POST" action="{{route('members.update', $user)}}">
        @csrf
        @method('put')
        <input type="hidden" name="organisation_id" value="{{$organisation->id}}">

        <div class="form-group">
            <label>Email</label>
            <input type="email" readonly class="form-control" value="{{$user->email}}">
        </div>

        <div class="form-group">
            <label>Organisation</label>
            <input type="text" readonly class="form-control" value="{{$organisation->name}}">
        </div>

        <div class="form-group">
            <label>Firstname</label>
            <input type="text" readonly class="form-control" value="{{$user->firstname}}">
        </div>

        <div class="form-group">
            <label>Lastname</label>
            <input type="text" readonly class="form-control" value="{{$user->lastname}}">
        </div>

        <div class="form-group">
            <label>Role</label>
            <div>
                @foreach ($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_role_id" value="{{$role->id}}" id="{{$role->id}}" @if ($role->is_selected) checked @endif>
                        <label class="form-check-label" for="{{$role->id}}">
                            {{$role->name}}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center">
            <a href="{{route('members.index')}}" class="btn btn-secondary">cancel</a>
            <input type="submit" class="btn btn-primary" value="@lang('update')">
        </div>

    </form>

@endsection
