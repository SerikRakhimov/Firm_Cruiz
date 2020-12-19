<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    @include('layouts.style_header')

</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">

            @foreach (session('glo_menu_lang') as $value)
                <a class="navbar-brand" href="{{ url('/setlocale/' . $value) }}">
                    <span
                        @if(session('locale') == $value)
                        style="text-decoration: underline"
                        @endif
                    >{{mb_strtoupper($value)}}</span>
                </a>
            @endforeach

            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            @auth
                <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('template.index') }}">{{trans('main.templates')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('base.index') }}">{{trans('main.my_projects')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('base.index') }}">{{trans('main.bases')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('item.index') }}">{{trans('main.items')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('link.index') }}">{{trans('main.links')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('main.index') }}">{{trans('main.mains')}}</a>
                        </li>
{{--                        <li class="nav-item">--}}
{{--                            <a class="nav-link" href="{{ route('order.index_archive_user') }}">Мой архив</a>--}}
{{--                        </li>--}}
                    </ul>
                @if(Auth::user()->isAdmin())
{{--                    <!-- Right Side Of Navbar -->--}}
{{--                        <ul class="navbar-nav ml-auto">--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link" href="{{ route('order.index_job_admin') }}"><span class="badge badge-primary">Перевод</span></a>--}}
{{--                            </li>--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link" href="{{ route('order.index_archive_admin') }}"><span class="badge badge-primary">Архив</span></a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
                @endif
            @endauth


            <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}<span class="caret"></span>
                            </a>
                            @if(Auth::user()->isAdmin())
                                <span class="badge badge-primary">администратор - переводчик</span>
                            @endif

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4 w-95 mx-auto">
{{--        <div class="mx-auto" style="width: 1200px;">--}}
        @yield('content')
{{--        </div>--}}
    </main>
</div>
<!-- Ajax -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>
