@extends('layouts.app')

@section('content')
    <p>
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.templates')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('template.create')}}'">
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
            <th class="text-center">{{trans('main.bases')}}</th>
            <th class="text-center">{{trans('main.projects')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $templates->firstItem() - 1;
        ?>
        @foreach($templates as $template)
            <?php
            $i++;
            ?>
            <tr>
                {{--                <th scope="row">{{$i}}</th>--}}
                <td class="text-center">
                    <a href="{{route('template.show',$template)}}" title="{{trans('main.show')}}">
                        {{$i}}
                    </a></td>
                <td class="text-left">
                    <a href="{{route('template.show',$template)}}" title="{{trans('main.show')}}">
                        {{$template->name()}}
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('base.index', $template)}}" title = "{{trans('main.bases')}}">
                        <img src="{{Storage::url('view_record.png')}}" width="15" height="15" alt = "{{trans('main.bases')}}">
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('project.index', $template)}}" title = "{{trans('main.projects')}}">
                        <img src="{{Storage::url('view_record.png')}}" width="15" height="15" alt = "{{trans('main.projects')}}">
                    </a>
                </td>
        @endforeach
        </tbody>
    </table>
    {{$templates->links()}}
@endsection

