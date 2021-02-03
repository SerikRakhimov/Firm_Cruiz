@extends('layouts.app')

@section('content')

    <?php
    use App\Models\Link;
    use App\Models\Item;
    use App\Http\Controllers\GlobalController;
    use App\Http\Controllers\ItemController;
    use App\Http\Controllers\MainController;
    ?>

    <h3 class="display-5">
        @if ($type_form == 'show')
            {{trans('main.viewing_record')}}
        @elseif($type_form == 'delete_question')
            {{trans('main.delete_record_question')}}?
        @endif
        <span class="text-info">-</span> <span class="text-success">{{$item->base->info()}}</span>
    </h3>
    <br>
    <?php
    $base = $item->base;
    $base_right = GlobalController::base_right($base);
    ?>
    <p>Id: <b>{{$item->id}}</b></p>
    @if($base_right['is_show_base_enable'] == true)
        <p>
            {{trans('main.code')}}: <b>{{$item->code}}</b><br>
            {{--        @foreach (session('glo_menu_save') as $key=>$value)--}}
            {{--            {{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$item['name_lang_' . $key]}}</b><br>--}}
            {{--        @endforeach--}}
            @if($base->type_is_photo)
                <a href="{{Storage::url($item->filename())}}">
                    <img src="{{Storage::url($item->filename())}}" height="250"
                         alt="" title="{{$item->title_img()}}">
                </a>
            @elseif($base->type_is_document)
                <a href="{{Storage::url($item->filename())}}" target="_blank">
                    Открыть документ
                </a>
            @else
                {{trans('main.name')}}: <b>{{$item->name()}}</b>
            @endif
            <br>
        </p>
    @endif

    {{--    @foreach($array_plan as $key=>$value)--}}
    {{--        <?php--}}
    {{--        $result = ItemController::get_items_for_link(Link::find($key));--}}
    {{--        $items = $result['result_parent_base_items'];--}}
    {{--        $item_work = Item::find($value);--}}
    {{--        ?>--}}
    {{--        --}}{{--    проверка нужна; для правильного вывода '$item_work->name()'--}}
    {{--        @if($item_work)--}}
    {{--            --}}{{--            <p>{{$result['result_parent_label']}} ({{$result['result_parent_base_name']}}):--}}
    {{--            <p>{{$result['result_parent_label']}}:--}}
    {{--                <b>{{$item_work->name()}}</b></p>--}}
    {{--        @endif--}}
    {{--    @endforeach--}}

    <p>
        @foreach($array_calc as $key=>$value)
            <?php

            $link = Link::find($key);
            $item_find = MainController::view_info($item->id, $key);
            ?>
            @if($link && $item_find)
                <?php
                $base_link_right = GlobalController::base_link_right($link);
                ?>
                @if($base_link_right['is_show_link_enable'] == true)
                    {{$link->parent_label()}}:
                        @if($link->parent_base->type_is_photo)
                            <br>
                            <a href="{{Storage::url($item_find->filename())}}">
                                <img src="{{Storage::url($item_find->filename())}}" height="250"
                                     alt="" title="{{$item_find->title_img()}}">
                            </a>
                        @elseif($link->parent_base->type_is_document)
                            <a href="{{Storage::url($item_find->filename())}}" target="_blank">
                                Открыть документ
                            </a>
                        @else
                            <b>{{$item_find->name()}}</b>
                        @endif
                        <br>
                @endif
            @endif
        @endforeach
    </p>

    <p>{{trans('main.date_created')}}:
        <b>{{$item->created_at->Format(trans('main.format_date'))}}</b>, {{mb_strtolower(trans('main.user'))}}:
        <b>{{$item->created_user->name()}}</b><br>
        {{trans('main.date_updated')}}:
        <b>{{$item->updated_at->Format(trans('main.format_date'))}}</b>, {{mb_strtolower(trans('main.user'))}}:
        <b>{{$item->updated_user->name()}}</b></p>

    <?php
    $result = ItemController::form_tree($item->id);
    echo $result;
    ?>

    @if ($type_form == 'show')
        <div class="mb-3 btn-group btn-group-sm">
            <a class="btn btn-primary" href="{{session('links')}}">{{trans('main.return')}}</a>
        </div>
    @elseif($type_form == 'delete_question')
        <form action="{{route('item.ext_delete',['item' => $item, 'heading' => $heading])}}" method="POST"
              id='delete-form'>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-primary">{{trans('main.delete')}}</button>
            <a class="btn btn-success" href="{{session('links')}}">{{trans('main.cancel')}}</a>
        </form>
    @endif

@endsection
