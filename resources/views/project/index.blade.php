@extends('layouts.app')

@section('content')
    <p>
    @include('layouts.template.show_name',['template'=>$template])
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.projects')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('project.create', ['template'=>$template])}}'">
                    {{--                    <i class="fa fa-plus fa-fw d-none d-sm-block "></i>--}}
                    {{trans('main.add')}}
                </button>
            </div>
        </div>
    </div>
    </p>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-left">{{trans('main.name')}}</th>
            <th class="text-left">{{trans('main.user')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $projects->firstItem() - 1;
        ?>
        @foreach($projects as $project)
            <?php
            $i++;
            ?>
            <tr>
                <td class="text-center">
                    <a href="{{route('project.show',$project)}}" title="{{trans('main.show')}}">
                        {{$i}}
                    </a></td>
                <td class="text-left">
                    <a href="{{route('project.show',$project)}}" title="{{trans('main.show')}}">
                        {{$project->name()}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('project.show',$project)}}" title="{{trans('main.show')}}">
                        {{$project->user->name}}
                    </a>
                </td>
        @endforeach
        </tbody>
    </table>
    {{$projects->links()}}
@endsection

