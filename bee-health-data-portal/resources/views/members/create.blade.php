@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <x-h1>Add user</x-h1>

    <form method="POST" action="{{route('members.store')}}">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="email">Email address</label>
            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" id="email" value="{{old('email')}}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="organisationSelect">Organisation</label>
            <select name="organisation_id" class="form-control @error('organisation_id') is-invalid @enderror" id="organisationSelect">
                @foreach ($organisations as $organisation)
                    <option value="{{$organisation->id}}">{{$organisation->name}} ({{$organisation->count}} {{Str::plural('member', $organisation->count)}})</option>
                @endforeach
            </select>
            @error('organisation_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Role</label>
            <div>
                @foreach ($user_roles as $user_role)
                    <div class="form-check @error('user_role_id') is-invalid @enderror">
                        <input @if ($user_role->is_selected) checked @endif class="form-check-input" type="radio" name="user_role_id" id="{{$user_role->id}}" value="{{$user_role->id}}">
                        <label class="form-check-label" for="{{$user_role->id}}">{{$user_role->name}}</label>
                    </div>
                @endforeach
                @error('user_role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="text-center">
            <input type="submit" class="btn btn-primary" value="Send invite">
        </div>
    </form>
@endsection
