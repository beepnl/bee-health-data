@extends('layouts.app')

@section('title', 'Login')

@section('carosel')
    @include('sites.carosel')
@endsection

@section('content')

    <form method="POST" action="{{ route('login.forgot.post') }}">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" aria-describedby="emailHelp">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <a class="d-block w-full text-right mb-2 font-italic font-weight-bold " href="{{ route('login') }}">Return to login</a>
        <div class="d-block text-center">
            <button type="submit" class="btn btn-primary btn-lg mx-auto">Reset your password</button>
        </div>
    </form>
@endsection
