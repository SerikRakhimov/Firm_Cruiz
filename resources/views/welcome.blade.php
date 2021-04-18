<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{config('app.name', 'Laravel')}}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title_big {
            font-size: 84px;
            color: #1c5145;
        }

        .title_small {
            font-size: 45px;
            color: #5f7a91;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">{{trans('main.mainmenu')}}</a>
            @else
                <a href="{{ route('login') }}">{{trans('main.login')}}</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">{{trans('main.register')}}</a>
                @endif
            @endauth
        </div>
    @endif

    <div class="content">
        <div class="title_big m-b-md">
            Abakus online
        </div>
        <div class="title_small m-b-md">
            {{trans('main.app_info_first')}}
        </div>
        <div class="title_small m-b-md">
            {{mb_strtolower(trans('main.app_info_second'))}}
        </div>
        <img src="{{Storage::url('logotype.png')}}" width="250" height="250" class="d-inline-block align-top" alt="" loading="lazy">
        <p><a href="mailto:support@abakusonline.com">support@abakusonline.com</a></p>
        {{--                <div class="title m-b-md">--}}
        {{--                    Abakus - учетная платформа--}}
        {{--                </div>--}}
        {{--                <div class="links">--}}
        <a href="https://www.instagram.com/abakusonline_com/">Instagram: abakusonline</a>
        {{--                    <a href="https://laracasts.com">Laracasts</a>--}}
        {{--                    <a href="https://laravel-news.com">News</a>--}}
        {{--                    <a href="https://blog.laravel.com">Blog</a>--}}
        {{--                    <a href="https://nova.laravel.com">Nova</a>--}}
        {{--                    <a href="https://forge.laravel.com">Forge</a>--}}
        {{--                    <a href="https://vapor.laravel.com">Vapor</a>--}}
        {{--                    <a href="https://github.com/laravel/laravel">GitHub</a>--}}
        {{--                </div>--}}
    </div>
</div>
</body>
</html>
