@extends('layouts.app')

@section('title', 'Login')

@section('content')

    <x-h1>Manage</x-h1>

    <ul class="nav flex-column text-center">
        <li class="nav-item">
            <a href="{{route('account.profile')}}" class="nav-link">{{__('My account')}}</a>
            <small>Change account details, download or remove</small>
        </li>

        <li class="nav-item">
            <a href="{{route('my_datasets.index')}}" class="nav-link">{{__('My datasets')}}</a>
            <small>All datasets I drafted or published</small>
        </li>

        <li class="nav-item">
            <a href="{{url('datasets?own_organisation=1')}}" class="nav-link">{{__('Organisation datasets')}}</a>
            <small>All datasets my organisation uploaded</small>
        </li>

        <li class="nav-item">
            <a href="{{route('my_access_requests.index')}}" class="nav-link">{{__('My access requests')}}</a>
            <small>Access requests I sent</small>
        </li>

        <li class="nav-item">
            <a href="{{route('notifications.index')}}" class="nav-link">{{__('My notifications')}}</a>
            <small>Alert on datasets</small>
        </li>

        @can('isAdmin')
        <li class="nav-item">
            <a href="{{url('account/members')}}" class="nav-link">{{__('Manage Users')}}</a>
            <small>Add, edit or remove users for your organisation</small>
        </li>
        <li class="nav-item">
                <a href="{{route('authorization_requests.index')}}" class="nav-link">{{__('Access requests')}}</a>
                <small>Requests for access to download for datasets for my organisation</small>
            </li>
        @endcan
        @can('isSuperAdmin')
        <li class="nav-item">
                <a href="{{url('account/organisations')}}" class="nav-link">{{__('Manage Organisation')}}</a>
                <small>Add or edit organisations</small>
        </li>
        <li class="nav-item">
            <a href="{{url('account/keywords')}}" class="nav-link">{{__('Manage Keywords')}}</a>
            <small>Add, edit or remove keywords</small>
        </li>
        <li class="nav-item">
            <a href="{{route('terms-and-conditions.edit')}}" class="nav-link">{{__('Manage Terms and Conditions')}}</a>
            <small>Update Terms and Conditions</small>
        </li>
        @endcan
    </ul>

@endsection
