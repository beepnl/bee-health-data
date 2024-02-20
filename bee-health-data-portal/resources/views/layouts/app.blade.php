<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} - @yield('title')</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/7fe67ae017.js" crossorigin="anonymous"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/28.0.0/classic/ckeditor.js"></script>
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ mix('css/app.css')  }}">
    </head>
    <body class="c-app">

        <div class="c-wrapper">
            <x-header class="c-header-fixed c-header-light px-3"/>
            @yield('carosel')
            <div class="c-body">
                <main class="c-main page--{{Str::slug(data_get(View::getSections(), 'title', 'default'))}}">
                    <!-- Main content here -->
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                @if (session('status'))
                                    <x-alert type="success">
                                        {{ session('status') }}
                                    </x-alert>
                                @endif
                                @if (session('warning'))
                                    <x-alert type="warning">
                                        {{ session('warning') }}
                                    </x-alert>
                                @endif
                            </div>
                        </div>
                        {{-- Page content --}}
                        @yield('content')
                    </div>
                </main>
            </div>
            <x-footer class="d-flex justify-content-around"/>
        </div>

        {{-- <script src="{{ asset('js/coreui.bundle.min.js') }}"></script> --}}
        <script src="{{asset('js/app.js')}}" ></script>
    </body>
</html>
