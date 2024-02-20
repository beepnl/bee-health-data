@extends('layouts.app')

@section('title', 'Login')

@section('carosel')
    @include('sites.carosel')
@endsection

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('login.post') }}">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required="required">
        </div>
        <a class="d-block w-full text-right mb-2 font-italic font-weight-bold" href="{{ route('login.forgot') }}">Forgot your password</a>
        <div class="d-block text-center">
            <button type="submit" class="btn btn-primary btn-lg">Submit</button>
        </div>
    </form>
@endsection
