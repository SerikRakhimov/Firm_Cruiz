@extends('layouts.app')

@section('content')
    @include('layouts.template.show_name',['template'=>$template])
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.bases')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                @if (Auth::user()->isAdmin())
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                            onclick="document.location='{{route('base.create', ['template'=>$template])}}'">
                        {{--                    <i class="fas fa-plus fa-fw d-none d-sm-block "></i>--}}
                        {{trans('main.add')}}
                    </button>
                @endif
            </div>
        </div>
    </div>
    </p>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-left">{{trans('main.names')}}</th>
            <th class="text-center">Id</th>
            <th class="text-center"></th>
            <th class="text-center"></th>
            <th class="text-center"></th>
            <th class="text-center"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $bases->firstItem() - 1;
        ?>
        @foreach($bases as $base)
            <?php
            $i++;
            ?>
            <tr>
                {{--                <th scope="row">{{$i}}</th>--}}
                <td class="text-center">{{$i}}</td>
                <td class="text-left">
                    <a href="{{route('item.base_index',$base)}}" title="{{$base->names()}}">
                        {{$base->names()}}
                    </a>
                </td>
                <td class="text-center">
                    {{$base->id}}
                </td>
                <td class="text-center">
                    <a href="{{route('base.show',$base)}}" title="{{trans('main.view')}}">
                        <img src="{{Storage::url('view_record.png')}}" width="15" height="15"
                             alt="{{trans('main.view')}}">
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('base.edit',$base)}}" title="{{trans('main.edit')}}">
                        <img src="{{Storage::url('edit_record.png')}}" width="15" height="15"
                             alt="{{trans('main.edit')}}">
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('base.delete_question',$base)}}" title="{{trans('main.delete')}}">
                        <img src="{{Storage::url('delete_record.png')}}" width="15" height="15"
                             alt="{{trans('main.delete')}}">
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('link.base_index',$base)}}" title="{{trans('main.links')}}">
                        <img src="{{Storage::url('links.png')}}" width="15" height="15" alt="{{trans('main.links')}}">
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$bases->links()}}
@endsection

