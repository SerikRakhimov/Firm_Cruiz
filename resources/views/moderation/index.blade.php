@extends('layouts.app')

@section('content')
    <p>
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.images')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
            </div>
        </div>
    </div>
    </p>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">{{trans('main.base')}}</th>
            <th class="text-center">{{trans('main.image')}}</th>
            <th class="text-center">{{trans('main.status')}}</th>
            <th class="text-left">{{trans('main.project')}}</th>
            <th class="text-left">{{trans('main.template')}}</th>
            <th class="text-left">{{trans('main.created_user_date')}}</th>
            <th class="text-left">{{trans('main.updated_user_date')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $items->firstItem() - 1;
        ?>
        @foreach($items as $item)
            <?php
            $i++;
            ?>
            <tr>
                {{--                <th scope="row">{{$i}}</th>--}}
                <td class="text-center">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$i}}
                    </a></td>
                <td class="text-center">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$item->base->name()}}
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{Storage::url($item->filename())}}">
                        <img src="{{Storage::url($item->filename(true))}}" height="50"
                             alt="" title="{{$item->filename(true)}}"></a>
                </td>
                <td class="text-center">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$item->status_img()}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$item->project->name()}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$item->project->template->name()}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$item->created_user_date()}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('moderation.show',$item)}}" title="{{trans('main.show')}}">
                        {{$item->updated_user_date()}}
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$items->links()}}
@endsection

