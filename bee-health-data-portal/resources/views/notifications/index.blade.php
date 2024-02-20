@extends('layouts.app')

@section('title', 'My Notifications')

@section('content')

    <x-h1>My notifications</x-h1>
    
    <form method="POST" action="{{route('notifications.update')}}">
        @method('PUT')
        @csrf
        <div class="form-group row mt-5">
            <div class="col-xs-12 col-lg-6 text-right">Notify me on:</div>
            <div class="col-xs-12 col-lg-6">
                @foreach ($name as $key => $field)
                <div class="form-check">
                    <input class="form-check-input" name="notifications[]" value="{{$key}}" type="checkbox" id="{{$key}}" @if(!empty($field['checked'])) checked @endif >
                    <label class="form-check-label" for="{{$key}}">
                        {{$field['label']}}
                    </label>
                    @if (!empty($field['description']))
                    <small id="emailHelp" class="form-text text-muted">{{$field['description']}}</small>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        <div class="form-group row mb-5">
            <div class="col-xs-12 col-lg-6 text-right">Frequency:</div>
            <div class="col-xs-12 col-lg-6">
                @foreach ($frequency as $key => $field)
                <div class="form-check">
                    <input class="form-check-input" name="frequency" value="{{$key}}" type="radio" id="{{$key}}" @if(!empty($field['checked'])) checked @endif >
                    <label class="form-check-label" for="{{$key}}">
                        {{$field['label']}}
                    </label>
                    @if (!empty($field['description']))
                    <small id="emailHelp" class="form-text text-muted">{{$field['description']}}</small>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="text-center">
            <input type="submit" class="btn btn-primary" value="@lang('save')">
        </div>

    </form>


@endsection
