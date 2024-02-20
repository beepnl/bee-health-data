@extends('layouts.app')

@section('title', __('Terms and Conditions'))


@section('content')
    
    <x-h1>{{ __('Terms and Conditions') }}</x-h1>

    {!! $content !!}

@endsection
