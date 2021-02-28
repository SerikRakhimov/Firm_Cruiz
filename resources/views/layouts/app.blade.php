<!doctype html>
<?php
use Illuminate\Support\Facades\App;
use App\Http\Controllers\GlobalController;
use Illuminate\Support\Facades\Session;
use App\Models\Project;
?>
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
            {{--            @foreach (session('glo_menu_lang') as $value)--}}
            @foreach (config('app.locales') as $value)
                <a class="navbar-brand" href="{{ url('/setlocale/' . $value) }}">
                    <span
                        {{--                        @if(session('locale') == $value)--}}
                        @if(App::getLocale() == $value)
                        style="text-decoration: underline"
                        @endif
                    >{{mb_strtoupper($value)}}</span>
                </a>
            @endforeach

            <a class="navbar-brand" href="{{ url('/') }}">
                {{config('app.name')}}
            </a>
            {{--                Этот <button> не удалять, нужен для связки с <div class="collapse navbar-collapse" id="navbarSupportedContent">--}}
            <button type="button" class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            @auth
                <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            {{--                            <a class="nav-link" style="color: green"--}}
                            {{--                            <a class="nav-link text-primary font-weight-bold"--}}
                            {{--                                <a class="nav-link text-primary"--}}
                            {{--                                   href="{{route('base.template_index', $glo_project_template_id)}}}">{{trans('main.bases')}}</a>--}}
                            <a class="nav-link" href="\home"
                               title="{{trans('main.info_project_role_selection')}}">
                                {{trans('main.project_role_selection')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('project.index_user', Auth::user())}}"
                            title="{{trans('main.info_projects')}}">
                                {{trans('main.projects')}}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('access.index_user', Auth::user())}}"
                            title="{{trans('main.info_accesses')}}">
                                {{trans('main.accesses')}}
                            </a>
                        </li>

                        {{--                        <li class="nav-item">--}}
                        {{--                            <a class="nav-link"--}}
                        {{--                               href="{{route('access.index_user', Auth::user())}}">{{trans('main.accesses')}}</a>--}}
                        {{--                        </li>--}}
                        {{--                        <li class="nav-item">--}}
                        {{--                            <a class="nav-link" href="#">{{trans('main.all_projects')}}</a>--}}
                        {{--                        </li>--}}
                    </ul>
                {{--                @if(Auth::user()->isAdmin())--}}
                {{--                    <!-- Right Side Of Navbar -->--}}
                {{--                        <ul class="navbar-nav ml-auto">--}}
                {{--                            <li class="nav-item">--}}
                {{--                                <a class="nav-link" href="{{route('template.index')}}">{{trans('main.templates')}}</a>--}}
                {{--                            </li>--}}
                {{--                            <li class="nav-item">--}}
                {{--                                <a class="nav-link" href="{{route('user.index')}}">{{trans('main.users')}}</a>--}}
                {{--                            </li>--}}
                {{--                        </ul>--}}
                {{--                @endif--}}
            @endauth
            <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            {{--                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>--}}
                            <a class="nav-link" href="{{ route('login') }}">{{trans('main.login')}}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{trans('main.register')}}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}<span class="caret"></span>
                            </a>
                            @if(Auth::user()->isAdmin())
                                <span class="badge badge-primary">{{trans('main.admin')}}</span>
                            @endif
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{trans('main.logout')}}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                                @auth
                                    {{--                                    <a class="dropdown-item" href="#">--}}
                                    {{--                                        {{trans('main.all_projects')}}--}}
                                    {{--                                    </a>--}}
                                    @if(Auth::user()->isModerator())
                                        <a class="dropdown-item" href="{{route('moderation.index')}}">
                                            {{trans('main.moderation')}}(<span
                                                class="badge badge-primary">{{trans('main.moderator')}}</span>)
                                        </a>
                                    @endif
                                    @if(Auth::user()->isAdmin())
                                        <a class="dropdown-item" href="{{route('template.index')}}">
                                            {{trans('main.templates')}}(<span
                                                class="badge badge-primary">{{trans('main.admin')}}</span>)
                                        </a>
                                        <a class="dropdown-item" href="{{route('user.index')}}">
                                            {{trans('main.users')}}(<span
                                                class="badge badge-primary">{{trans('main.admin')}}</span>)
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    <main class="py-4 w-75 mw-75 mx-auto">
        {{--                <div class="mx-auto" style="width: 1200px;">--}}
        @yield('content')
        {{--                </div>--}}
    </main>
</div>
<!-- Ajax -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
</body>
</html>
