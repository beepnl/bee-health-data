<header {{$attributes->merge(['class' => 'c-header'])}}>
    <a class="c-header-brand text-warning text-decoration-none text-waring-hover-none" href="{{ route('home') }}">
        <img src="{{ asset('images/brand.svg') }}" height="40" class="d-inline-block align-top pr-2" alt="{{ config('app.name') }}">
        {{ config('app.name') }}
    </a>
    <div class="mr-auto">
    </div>
    <div class="d-flex align-items-center">
        @auth
            <a href="{{ route('datasets.create') }}" class="btn btn-ghost-dark">{{__('Upload')}}</a>
            <a href="{{ route('account.index') }}" class="btn btn-ghost-dark">{{__('My account')}}</a>
            <a href="{{ url('/logout') }}" class="btn btn-ghost-dark">{{__('Logout')}}</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-ghost-dark">{{__('Login')}}</a>
        @endauth
    </div>
</header>
