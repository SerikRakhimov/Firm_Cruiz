@extends('layouts.app')

@section('content')

    <?php
    use App\Models\Link;
    use App\Models\Item;
    use App\Http\Controllers\ItemController;
    use App\Http\Controllers\MainController;
    ?>

    <h3 class="display-5">
        @if ($type_form == 'show')
            {{trans('main.viewing_record')}}
        @elseif($type_form == 'delete_question')
            {{trans('main.delete_record_question')}}?
        @endif
        <span class="text-info">-</span> <span class="text-success">{{trans('main.item')}}</span>
    </h3>
    <br>

    <p>Id: <b>{{$item->id}}</b></p>
    <p>{{trans('main.base')}}: <b>{{$item->base->info()}}</b>
    <div><b>{{$item->base->info_full()}}</b></div></p>
    <p>{{trans('main.code')}}: <b>{{$item->code}}</b></p>

    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$item['name_lang_' . $key]}}</b></p>
    @endforeach

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

    @foreach($array_calc as $key=>$value)
        <?php

        $link = Link::find($key);
        $item_find = MainController::view_info($item->id, $key);
        ?>
        @if($link && $item_find)
            <p>{{$link->parent_label()}}:
                <b>{{$item_find->name()}}</b></p>

        @endif
    @endforeach



    <p>{{trans('main.date_created')}}: <b>{{$item->created_at}}</b></p>
    <p>{{trans('main.date_updated')}}: <b>{{$item->updated_at}}</b></p>

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
