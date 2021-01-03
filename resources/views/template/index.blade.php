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
{{--                        "d-inline" нужно, чтобы иконка и текст были на одной линии--}}
                        onclick="document.location='{{route('template.create')}}'"><i class="fas fa-plus d-inline"></i>&nbsp;{{trans('main.add')}}
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
            <th class="text-center">{{trans('main.roles')}}</th>
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
                    <a href="{{route('base.index', $template)}}" title="{{trans('main.bases')}}">
                        <i class="fas fa-scroll"></i>
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('role.index', $template)}}" title="{{trans('main.roles')}}">
                        <i class="fas fa-user-circle"></i>
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('project.index_template', $template)}}" title="{{trans('main.projects')}}">
                        <i class="fas fa-project-diagram"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$templates->links()}}
@endsection

