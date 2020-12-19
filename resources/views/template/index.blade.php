@extends('layouts.app')

@section('content')
    <p>
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-left align-top">
                <h3>{{trans('main.templates')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('template.create')}}'">
                    {{--                    <i class="fa fa-plus fa-fw"></i>--}}
                    {{trans('main.add')}}
                </button>
            </div>
        </div>
    </div>
    </p>
    <div class="container">
        <div class="row">
            <div class="col-5 text-left align-top">
                <h3>{{trans('main.templates')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('template.create')}}'">
{{--                    <i class="fa fa-plus fa-fw"></i>--}}
                    {{trans('main.add')}}
                </button>
            </div>
        </div>
    </div>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-left">{{trans('main.name')}}</th>
            {{--            <th class="text-center">Id</th>--}}
            {{--            <th class="text-center"></th>--}}
            {{--            <th class="text-center"></th>--}}
            {{--            <th class="text-center"></th>--}}
            {{--            <th class="text-center"></th>--}}
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
            {{--                <td class="text-center">--}}
            {{--                    {{$template->id}}--}}
            {{--                </td>--}}
            {{--                <td class="text-center">--}}
            {{--                    <a href="{{route('base.show',$base)}}" title = "{{trans('main.view')}}">--}}
            {{--                        <img src="{{Storage::url('view_record.png')}}" width="15" height="15" alt = "{{trans('main.view')}}">--}}
            {{--                    </a>--}}
            {{--                </td>--}}
            {{--                <td class="text-center">--}}
            {{--                    <a href="{{route('base.edit',$base)}}" title = "{{trans('main.edit')}}">--}}
            {{--                        <img src="{{Storage::url('edit_record.png')}}" width="15" height="15" alt = "{{trans('main.edit')}}">--}}
            {{--                    </a>--}}
            {{--                </td>--}}
            {{--                <td  class="text-center">--}}
            {{--                    <a href="{{route('base.delete_question',$base)}}" title = "{{trans('main.delete')}}">--}}
            {{--                        <img src="{{Storage::url('delete_record.png')}}" width="15" height="15" alt = "{{trans('main.delete')}}">--}}
            {{--                    </a>--}}
            {{--                </td>--}}
            {{--                <td  class="text-center">--}}
            {{--                    <a href="{{route('link.base_index',$base)}}" title = "{{trans('main.links')}}">--}}
            {{--                        <img src="{{Storage::url('links.png')}}" width="15" height="15" alt = "{{trans('main.links')}}">--}}
            {{--                    </a>--}}
            {{--                </td>--}}
            {{--            </tr>--}}
        @endforeach
        </tbody>
    </table>
    {{$templates->links()}}
@endsection

