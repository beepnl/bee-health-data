@extends('layouts.app')

@section('title', 'Login')

@section('carosel')
    @include('sites.carosel')
@endsection

@section('content')

    <x-h1>Reset Your Password</x-h1>

    <form method="POST" action="{{ route('login.reset.post', $id) }}">
        @method('PUT')
        @csrf
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required="required">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Re-type new Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required="required">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="row">
            <div class="col-6">
                <button type="submit" class="btn btn-primary mx-auto">Reset password</button>
            </div>
            <div class="col-6">
                <a class="inline-block w-full text-right mb-2 italic " href="{{ route('login') }}">Return to login</a>
            </div>
        </div>
    </form>
@endsection
