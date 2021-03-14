@extends('layouts.app')

@section('content')
    <?php

    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <h3>{{$title}}</h3>
            </div>
        </div>
    </div>
    </p>
    <?php
    $i = $projects->firstItem() - 1;
    ?>
    {{--    <div class="card-deck">--}}
    @foreach($projects as $project)
        <?php
        $i++;
        ?>
        <div class="card">
            <p class="card-header">{{$project->template->name()}}</p>
            <div class="card-body">
                <h4 class="card-title">{{$project->name()}}</h4>
                <p class="card-text">{{$project->desc()}}</p>
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='#'">
                    <i class="fas fa-plus d-inline"></i>
                    {{trans('main.add')}}
                </button>
            </div>
            <div class="card-footer">
                <small class="text-muted">{{$project->created_at}}</small>
            </div>
        </div>
        <br>

    @endforeach

    {{--    </div>--}}

    {{--    <div class="card">--}}
    {{--        <h3 class="card-header">Featured</h3>--}}
    {{--        <div class="card-block">--}}
    {{--            <h4 class="card-title">Special title treatment</h4>--}}
    {{--            <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>--}}
    {{--            <a href="#" class="btn btn-primary">Go somewhere</a>--}}
    {{--        </div>--}}
    {{--        <div class="card-footer">--}}
    {{--            <small class="text-muted">{{$project->created_at}}</small>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--        <div class="card bg-primary">--}}
    {{--            <div class="card-body text-center">--}}
    {{--                <p class="card-text">Some text inside the first card</p>--}}
    {{--                <p class="card-text">Some more text to increase the height</p>--}}
    {{--                <p class="card-text">Some more text to increase the height</p>--}}
    {{--                <p class="card-text">Some more text to increase the height</p>--}}
    {{--            </div>--}}
    {{--        </div>--}}

    {{$projects->links()}}
@endsection

