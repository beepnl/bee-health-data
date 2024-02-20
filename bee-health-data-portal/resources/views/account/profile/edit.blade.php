@extends('layouts.app')

@section('title', 'Login')

@section('content')

<x-h1>{{$account_is_activated ? 'Update account' : 'Activate account'}}</x-h1>

@if($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
@endif
    <form method="POST" action="{{ $account_is_activated ? route('account.update', $id) : route('account.activate.post', $id) }}">
        @method('PUT')
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <div>
                <input type="text" class="form-control-plaintext"  value="{{ $email }}" readonly>
            </div>
        </div>
        @if ($organisations)
        <div class="form-group">
            <label for="organisation">Organisations</label>
                <ul class="list-disc">
                @foreach ($organisations as $organisation)
                    <li class="ml-4">{{ $organisation['name'] }} (role: {{ App\Models\UserRole::find($organisation['pivot']['user_role'])->name }})</li>
                @endforeach
                </ul>
        </div>
        @endif
        <div class="form-group">
            <label for="firstname">First name</label>
            <input type="text" class="form-control @error('firstname') is-invalid @enderror" id="firstname" name="firstname" aria-describedby="firstnameHelp" value="{{ old('firstname', $firstname) }}">
            @error('firstname')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="lastname">Last name</label>
            <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="lastname" name="lastname" aria-describedby="lastnameHelp" value="{{ old('lastname', $lastname) }}">
            @error('lastname')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if($account_is_activated)
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" class="form-control @error('old_password') is-invalid @enderror" id="old_password" name="old_password">
            </div>
            @error('old_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif

        <div class="form-group">
            <label for="password">New password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Re-type new Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password_confirmation" name="password_confirmation" >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if (!$account_is_activated)
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" name="accepted_terms_and_conditions" id="accepted_terms_and_conditions">
            <label class="form-check-label" for="accepted_terms_and_conditions">I accept the <a href="{{url('terms-and-conditions')}}" target="_blank">terms and conditions</a></label>
            </div>
        @endif

        @if($account_is_activated)
            <div class="text-center mb-4">
                <button type="submit" class="btn btn-primary mx-auto btn-lg">Update</button>
            </div>
            <div class="text-center">
                <a href="{{url('download-account-data-export')}}" class="btn btn-primary mx-auto btn-lg">Download account data</a>
            </div>
            <div class="text-center mb-4">
                <p>An email is sent to you with download links</p>
                <p>to the data and information which is part of</p>
                <p>or linked to your account</p>
            </div>
        @else
            <div class="text-center mb-4">
                <button type="submit" class="btn btn-primary mx-auto btn-lg">Activate</button>
            </div>
        @endif
    </form>
     @if($account_is_activated)
        <form method="POST" action="{{route('account.delete', ['user' => $id])}}">
            @csrf
            @method('delete')
            <div class="text-center mb-4">
                <input type="submit" class="btn btn-danger mx-auto btn-lg" value="Remove account">
            </div>
        </form>
     @endif
@endsection
