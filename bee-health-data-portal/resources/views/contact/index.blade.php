@extends('layouts.app')

@section('title', __('Manage users'))

@section('carosel')
    @include('sites.carosel')
@endsection

@section('content')
    <x-h1>Contact form</x-h1>
    @if (!session('is_send'))

        <p class="text-center mb-4">Enter your, questions or issues on the {{config('app.name')}}</p>

        <form method="POST" action="{{route('contact.store')}}">
            @csrf

            <div class="form-group">
                <label for="comment">Comment</label>
                <textarea class="form-control @error('body') is-invalid @enderror" name="body" id="comment" rows="5"></textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-center">
                <input type="submit" class="btn btn-primary btn-lg" value="Send">
            </div>

        </form>
    @else
        <div class="text-center">
            <div>{!!__('messages.send_contact_successfully')!!}</div>
            <a href="{{url('/')}}" class="btn btn-link">Go to homepage</a>
        </div>
    @endif

@endsection
