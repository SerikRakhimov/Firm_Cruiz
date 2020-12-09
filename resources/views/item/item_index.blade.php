@extends('layouts.app')

@section('content')
    <?php
    use App\Models\Item;
    use App\Models\Link;
    use App\Models\Main;
    use \App\Http\Controllers\MainController;
    $links = $item->base->child_links->sortBy('parent_base_number');
    function objectToarray($data)
    {
        $array = (array)$data;
        return $array;
    }
    ?>

    @if(count($links) !=0)
        <table class="table table-sm table-borderless">
            <thead>
            <tr>
                @foreach($links as $link)
                    <th>
                        <a href="{{route('item.base_index',$link->parent_base_id)}}"
                           title="{{$link->parent_base->names()}}">
                            {{$link->parent_label()}}:
                        </a>
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            <tr>
                @foreach($links as $link)
                    <td>
                        {{--                        <?php--}}
                        {{--                        $main_first = Main::all()->where('child_item_id', $item->id)->where('link_id', $link->id)->first();--}}
                        {{--                        ?>--}}
                        {{--                        @if($main_first != null)--}}
                        {{--                            <a href="{{route('item.item_index', $main_first->parent_item)}}">--}}
                        {{--                                @endif--}}
                        {{--                                {{$main_first != null ? $main_first->parent_item->name() : ""}}--}}
                        {{--                                @if($main_first != null)--}}
                        {{--                            </a>--}}
                        {{--                        @endif--}}
                        <?php
                        $item_find = MainController::view_info($item->id, $link->id);
                        ?>
                        @if($item_find)
                            {{--проверка на вычисляемые поля--}}
                            @if($link->parent_is_parent_related == false)
                                <a href="{{route('item.item_index', ['item'=>$item_find,'par_link'=>$link])}}">
                                    @else
                                        <a href="{{route('item.item_index', $item_find)}}">
                                            @endif
                                            {{$item_find->name()}}
                                        </a>
                            @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach($links as $link)
                    <td>
                        &#8195; &#8195; &#8195; &#8595;
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>
        {{--    @endif--}}
        {{--    <hr align="center" width="100%" size="2" color="#ff0000"/>--}}
        {{--        &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195; &#8595;	&#8195;--}}
        <hr>
    @endif
    {{--    <p>--}}
    {{--    <div class="container-fluid">--}}
    {{--        <div class="row">--}}
    {{--            <div class="col text-left">--}}
    {{--                <h3>--}}
    {{--                    <a href="{{route('item.base_index', $item->base_id)}}" title="{{$item->base->names()}}">--}}
    {{--                        {{$item->base->name()}}:--}}
    {{--                    </a>--}}
    {{--                    {{$item->name()}}--}}
    {{--                </h3>--}}
    {{--            </div>--}}
    {{--            <div class="col-1 text-center">--}}
    {{--                <a href="{{route('item.ext_create', ['base'=>$item->base_id,'heading'=>intval(true)])}}"--}}
    {{--                   title="{{trans('main.add')}}">--}}
    {{--                    <img src="{{Storage::url('add_record.png')}}" width="15" height="15" alt="{{trans('main.add')}}">--}}
    {{--                </a>--}}
    {{--            </div>--}}
    {{--            <div class="col-1 text-center">--}}
    {{--                <a href="{{route('item.ext_show', $item)}}" title="{{trans('main.view')}}">--}}
    {{--                    <img src="{{Storage::url('view_record.png')}}" width="15" height="15"--}}
    {{--                         alt="{{trans('main.view')}}">--}}
    {{--                </a>--}}
    {{--            </div>--}}
    {{--            <div class="col-1 text-center">--}}
    {{--                <a href="{{route('item.ext_edit', $item)}}" title="{{trans('main.edit')}}">--}}
    {{--                    <img src="{{Storage::url('edit_record.png')}}" width="15" height="15"--}}
    {{--                         alt="{{trans('main.edit')}}">--}}
    {{--                </a>--}}
    {{--            </div>--}}
    {{--            <div class="col-1 text-center">--}}
    {{--                <a href="{{route('item.ext_delete_question', ['item' => $item, 'heading'=> true])}}"--}}
    {{--                   title="{{trans('main.delete')}}">--}}
    {{--                    <img src="{{Storage::url('delete_record.png')}}" width="15" height="15"--}}
    {{--                         alt="{{trans('main.delete')}}">--}}
    {{--                </a>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--    </p>--}}
    {{--    <hr>--}}
    {{--    <p class="text-center">&#8595;</p>--}}

    <?php
    $link2 = null;  // нужно
    if (@$par_link) {
        $link2 = $par_link;
        $link2 = Link::find($link2->id);  // проверка существования в базе данных
    }
    if (!$link2) {
        // Находим заполненный подчиненный link
        $links = $item->base->parent_links;
        if (count($links) > 0) {

            $next_links_fact1 = DB::table('mains')
                ->select('link_id')
                ->where('parent_item_id', $item->id)
                ->distinct()
                ->get()
                ->groupBy('link_id');
            // Если найдены - берем первый
            if (count($next_links_fact1) > 0) {
                $link2 = Link::find($next_links_fact1->first()[0]->link_id);
                // Если не найдены - берем первый пустой (без данных)
            } else {
                $link2 = $links[0];
            }
        };
    }
    ?>
    <p>
    <div class="container-fluid">
        <div class="row">
            <div class="col text-left">
                <h3>
                    <a href="{{route('item.base_index', $item->base_id)}}" title="{{$item->base->names()}}">
                        @if($link2)
                            {{$link2->parent_label()}}:
                        @else
                            {{$item->base->name()}}:
                        @endif
                    </a>
                    {{$item->name()}}
                </h3>
            </div>
            <div class="col-1 text-center">
                <a href="{{route('item.ext_create', ['base'=>$item->base_id,'heading'=>intval(true)])}}"
                   title="{{trans('main.add')}}">
                    <img src="{{Storage::url('add_record.png')}}" width="15" height="15"
                         alt="{{trans('main.add')}}">
                </a>
            </div>
            <div class="col-1 text-center">
                <a href="{{route('item.ext_show', $item)}}"
                   title="{{trans('main.view')}}{{$item->base->is_code_needed?" (".trans('main.code')." = ".$item->code.")":""}}">
                    <img src="{{Storage::url('view_record.png')}}" width="15" height="15"
                         alt="{{trans('main.view')}}">
                </a>
            </div>
            <div class="col-1 text-center">
                <a href="{{route('item.ext_edit', $item)}}" title="{{trans('main.edit')}}">
                    <img src="{{Storage::url('edit_record.png')}}" width="15" height="15"
                         alt="{{trans('main.edit')}}">
                </a>
            </div>
            <div class="col-1 text-center">
                <a href="{{route('item.ext_delete_question', ['item' => $item, 'heading'=> true])}}"
                   title="{{trans('main.delete')}}">
                    <img src="{{Storage::url('delete_record.png')}}" width="15" height="15"
                         alt="{{trans('main.delete')}}">
                </a>
            </div>
        </div>
    </div>
    </p>
    @if($link2)
        <hr>
        <div class="text-center">&#8595;</div>

        <?php
        $mains = Main::all()->where('parent_item_id', $item->id)->where('link_id', $link2->id)->sortBy(function ($main) {
            return $main->link->child_base->name() . $main->child_item->name();
        });
        ?>

        <p>
        <div class="container-fluid">
            <div class="row">
                <div class="col text-left">
                    <h3>
                    </h3>
                    <h3>
                        <a href="{{route('item.base_index',$link2->child_base_id)}}"
                           title="{{$link2->child_base->names()}}">
                            {{$link2->child_labels()}}
                        </a>
                        ({{$link2->parent_label()}} = {{$item->name()}}):
                    </h3>
                </div>
            </div>
        </div>
        </p>
        <p>
        <div class="container-fluid">
            <div class="row">
                <div class="col text-right">
                    <a href="{{route('item.ext_create', ['base'=>$link2->child_base_id, 'heading'=>intval(false), 'par_link'=>$link2->id, 'parent_item'=>$item->id])}}"
                       title="{{trans('main.add')}}">
                        <img src="{{Storage::url('add_record.png')}}" width="15" height="15"
                             alt="{{trans('main.add')}}">
                    </a>
                </div>
            </div>
        </div>
        </p>

        @if (count($mains) > 0)
            <table class="table table-sm table-bordered table-hover">
                <caption>{{trans('main.select_record_for_work')}}</caption>
                <?php
                $links1 = $link2->child_base->child_links->where('id', '!=', $link2->id)->sortBy('parent_base_number');
                //$links1 = $link2->child_base->child_links;
                ?>
                <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-left">{{$link2->child_label()}}</th>
                    <th class="text-center"></th>
                    @foreach($links1 as $link1)
                        <th>
                            <a href="{{route('item.base_index',$link1->parent_base_id)}}"
                               title="{{$link1->parent_base->names()}}">
                                {{$link1->parent_label()}}
                            </a>
                        </th>
                    @endforeach
                    <th class="text-center"></th>
                    <th class="text-center"></th>
                    <th class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = 0;
                ?>
                @foreach($mains as $main)
                    <?php
                    $item1_id = $main->child_item_id;
                    $item1 = Item::find($item1_id);
                    $i++;
                    ?>
                    <tr>
                        <td class="text-center">{{$i}}</td>
                        <td class="text-left">
                            <a href="{{route('item.item_index', $item1)}}">
                                {{$item1->name()}}
                            </a>
                        </td>
                        <td class="text-center">&#8594;</td>
                        @foreach($links1 as $link1)
                            <td>
                                <?php
                                $item_find = MainController::view_info($item1->id, $link1->id);
                                ?>
                                @if($item_find)
                                    {{--проверка на вычисляемые поля--}}
                                    @if($link1->parent_is_parent_related == false)
                                        <a href="{{route('item.item_index', ['item'=>$item_find,'par_link'=>$link1])}}">
                                            @else
                                                <a href="{{route('item.item_index', $item_find)}}">
                                                    @endif
                                                    {{$item_find->name()}}
                                                </a>
                                    @endif
                            </td>

                        @endforeach
                        <td class="text-center">
                            <a href="{{route('item.ext_show', $item1)}}" title="{{trans('main.view')}}">
                                <img src="{{Storage::url('view_record.png')}}" width="15" height="15"
                                     alt="{{trans('main.view')}}">
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="{{route('item.ext_edit',['item'=>$item1, 'heading'=>intval(false), 'par_link'=>$link2, 'parent_item'=>$item])}}"
                               title="{{trans('main.edit')}}">
                                <img src="{{Storage::url('edit_record.png')}}" width="15" height="15"
                                     alt="{{trans('main.edit')}}">
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="{{route('item.ext_delete_question', $item1)}}"
                               title="{{trans('main.delete')}}">
                                <img src="{{Storage::url('delete_record.png')}}" width="15" height="15"
                                     alt="{{trans('main.delete')}}">
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
        <hr>


        <?php

        //      $next_links_plan = $item->base->parent_links->where('id', '!=', $link2->id);
        // исключить вычисляемые поля
        $next_links_plan = $item->base->parent_links->where('parent_is_parent_related', false)->where('id', '!=', $link2->id);

        $next_links_fact = DB::table('mains')
            ->select('link_id')
            ->where('parent_item_id', $item->id)
            ->where('link_id', '!=', $link2->id)
            ->distinct()
            ->get()
            ->groupBy('link_id');

        $array = objectToarray($next_links_fact);
        ?>
        @if (!count($next_links_plan) == 0)
            <form action="{{route('item.store_link_change')}}" method="POST" enctype=multipart/form-data>
                <div class="form-row">
                    @csrf
                    <input type="hidden" name="item" value="{{$item->id}}">

                    <div class="d-flex justify-content-end align-items-center mt-5">
                        <div class="col-auto">
                            <label for="link">{{trans('main.another_attitude')}} = </label>
                        </div>
                        <div class="">
                            <select class="form-control"
                                    name="link"
                                    id="link"
                                    class="form-control @error('link') is-invalid @enderror">
                                @foreach($next_links_plan as $key=>$value)
                                    <option value="{{$value->id}}"
                                        {{--                                                                                    @if(!isset($array["\x00*\x00items"][$value->id]))--}}
                                        {{--                                                                                    disabled--}}
                                        {{--                                                                                @endif--}}
                                    >
                                        {{--                                                                                {{$value->parent_label()}} {{$main->child_item->name()}} ({{mb_strtolower(trans('main.on'))}} {{$value->child_labels()}})--}}
                                        {{$value->child_labels()}} ({{$value->parent_label()}})
                                        @if(isset($array["\x00*\x00items"][$value->id]))
                                            *
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('link')
                            <div class="text-danger">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="col-2 ml-auto">
                            <button type="submit" class="btn btn-primary"
                            >{{trans('main.select')}}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    @endif
@endsection
