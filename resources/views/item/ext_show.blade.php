@extends('layouts.app')

@section('content')

    <?php
    use App\Models\Link;
    use App\Models\Item;
    use App\Http\Controllers\GlobalController;
    use App\Http\Controllers\ItemController;
    use App\Http\Controllers\MainController;
    $project = $item->project;
    $base_right = GlobalController::base_right($item->base, $role);
    ?>
    @include('layouts.show_project_role',['project'=>$project, 'role'=>$role])
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
    $base_right = GlobalController::base_right($base, $role);
    ?>
    <p>Id: <b>{{$item->id}}</b></p>
    @if($base_right['is_show_base_enable'] == true)
        <p>
            @if($base->is_code_needed == true)
                {{trans('main.code')}}: <b>{{$item->code}}</b><br>
            @endif
            {{--        @foreach (config('app.locales') as $key=>$value)--}}
            {{--            {{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$item['name_lang_' . $key]}}</b><br>--}}
            {{--        @endforeach--}}
            @if($base->type_is_image)
                @include('view.img',['item'=>$item, 'size'=>"medium", 'filenametrue'=>false])
                {{--                <a href="{{Storage::url($item->filename())}}">--}}
                {{--                    <img src="{{Storage::url($item->filename())}}" height="250"--}}
                {{--                         alt="" title="{{$item->title_img()}}">--}}
                {{--                </a>--}}
            @elseif($base->type_is_document)
                @include('view.doc',['item'=>$item])
                {{--                <a href="{{Storage::url($item->filename())}}" target="_blank">--}}
                {{--                    Открыть документ--}}
                {{--                </a>--}}
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
                $base_link_right = GlobalController::base_link_right($link, $role);
                ?>
                @if($base_link_right['is_show_link_enable'] == true)
                    {{$link->parent_label()}}:
                    @if($link->parent_base->type_is_image)
                        {{--                            <br>--}}
                        @include('view.img',['item'=>$item_find, 'size'=>"medium", 'filenametrue'=>false])
                        {{--                            <a href="{{Storage::url($item_find->filename())}}">--}}
                        {{--                                <img src="{{Storage::url($item_find->filename())}}" height="250"--}}
                        {{--                                     alt="" title="{{$item_find->title_img()}}">--}}
                        {{--                            </a>--}}
                    @elseif($link->parent_base->type_is_document)
                        @include('view.doc',['item'=>$item_find])
                        {{--                            <a href="{{Storage::url($item_find->filename())}}" target="_blank">--}}
                        {{--                                Открыть документ--}}
                        {{--                            </a>--}}
                    @else
                        <b>{{$item_find->name()}}</b>
                    @endif
                    <br>
                @endif
            @endif
        @endforeach
    </p>

    <p>{{trans('main.created_user_date_time')}}:
        <b>{{$item->created_user_date_time()}}</b><br>
        {{trans('main.updated_user_date_time')}}:
        <b>{{$item->updated_user_date_time()}}</b></p>

    <?php
//        Не удалять
    $result = ItemController::form_tree($item->id);
    echo $result;
    ?>
    @if ($type_form == 'show')
        <p>
            @if($base_right['is_list_base_update'] == true)
                <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                        onclick='document.location="{{route('item.ext_edit', ['item'=>$item, 'role'=>$role])}}"'
                        title="{{trans('main.edit')}}">
                    <i class="fas fa-edit"></i>
                    {{trans('main.edit')}}
                </button>
            @endif
            @if(ItemController::is_delete($item, $role) == true)
                <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                        onclick='document.location="{{route('item.ext_delete_question', ['item'=>$item, 'role'=>$role])}}"'
                        title="{{trans('main.delete')}}">
                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
            @endif
{{--                С base_index.blade.php--}}
                {{--                                Не удалять: просмотр Пространство--}}
                {{--                                                                            проверка, если link - вычисляемое поле--}}
                {{--                                    @if ($link->parent_is_parent_related == true || $link->parent_is_numcalc == true)--}}
                {{--                                        <a href="{{route('item.item_index', ['item'=>$item_find, 'role'=>$role])}}">--}}
                {{--                                            @else--}}
                {{--                                                <a href="{{route('item.item_index', ['item'=>$item_find, 'role'=>$role,'par_link'=>$link])}}">--}}
                {{--                                                    @endif--}}

                <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                    onclick='document.location="{{route('item.item_index', ['item'=>$item, 'role'=>$role])}}"'
                    title="{{trans('main.space')}}">
                <i class="fas fa-atlas"></i>
                {{trans('main.space')}}
            </button>
            <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                    title="{{trans('main.cancel')}}" @include('layouts.item.base_index.previous_url')>
                <i class="fas fa-arrow-left"></i>
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('item.ext_delete',['item' => $item, 'role' => $role, 'heading' => $heading])}}"
              method="POST"
              id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.item.base_index.previous_url')>
                    <i class="fas fa-arrow-left"></i>
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
