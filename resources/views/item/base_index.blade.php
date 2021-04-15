@extends('layouts.app')

@section('content')
    <?php
    use App\Models\Base;
    use App\Models\Item;
    use App\Models\Link;
    use App\Models\Main;
    use \App\Http\Controllers\GlobalController;
    use \App\Http\Controllers\ItemController;
    use \App\Http\Controllers\MainController;
    $links = $base->child_links->sortBy('parent_base_number');
    $base_right = GlobalController::base_right($base, $role);
    ?>
    @include('layouts.show_project_role',['project'=>$project, 'role'=>$role])
    <div class="container-fluid">
        <div class="row">
            <div class="col text-left align-top">
                <h3>{{$base->names()}}</h3>
            </div>
        </div>
        @if($base_right['is_list_base_create'] == true)
            <div class="col-12 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('item.ext_create', ['base'=>$base, 'project'=>$project, 'role'=>$role])}}'">
                    <i class="fas fa-plus d-inline"></i>&nbsp;{{trans('main.add')}}
                </button>
            </div>
        @endif
        {{--        Не удалять--}}
        @if(1==1)
            @auth
                @if ($role->is_author())
                    @if ($base->is_calcname_lst == true)
                        <div class="col-12 text-right">
                            <a href="{{route('item.calculate_name', ['base'=>$base, 'project'=>$project])}}"
                               title="{{trans('main.calculate_name')}}">
                                <img src="{{Storage::url('calculate_name.png')}}" width="15" height="15"
                                     alt="{{trans('main.calculate_name')}}">
                            </a>
                        </div>
                    @endif
                    @if ($base->is_recalc_code == true)
                        <div class="col-12 text-right">
                            <a href="{{route('item.recalculation_codes',['base'=>$base, 'project'=>$project])}}"
                               title="{{trans('main.recalculation_codes')}}">
                                <img src="{{Storage::url('recalculation_codes.png')}}" width="15" height="15"
                                     alt="{{trans('main.recalculation_codes')}}">
                            </a>
                        </div>
                    @endif
                @endif
                @if (Auth::user()->isAdmin())
                    <div class="col-12 text-right">
                        <a href="{{route('item.verify_table_texts')}}" title="{{trans('main.verify_table_texts')}}">
                            {{trans('main.verify_table_texts')}}"
                        </a>
                    </div>
                @endif
            @endauth
        @endif
    </div>
    </p>
    <?php
    $tile_view = $base->tile_view($base_right);
    $link_image = $tile_view['link'];
    $i = $items->firstItem() - 1;
    ?>
    <!---->
    {{--    <p>Выберите любимого персонажа:</p>--}}
    {{--    <p><input list="character">--}}
    {{--        <datalist id="character">--}}
    {{--            <option value="Чебурашка"></option>--}}
    {{--            <option value="Крокодил Гена"></option>--}}
    {{--            <option value="Шапокляк"></option>--}}
    {{--        </datalist></p>--}}
    {{--    <details>--}}
    {{--        <summary>Информация об авторе</summary>--}}
    {{--        <p>Бендер Родригез</p>--}}
    {{--    </details>--}}
    @if($tile_view['result'] == true)
        <div class="card-columns">
            @foreach($items as $item)
                <?php
                $i = $i + 1;
                $item_find = MainController::view_info($item->id, $link_image->id);
                ?>
                {{--                <div class="card text-center">--}}
                {{--                    <div class="card card-inverse text-center" style="background-color: rgba(222,255,162,0.23); border-color: #3548ee;">--}}
                <div class="card shadow">
                    @if($base->is_code_needed == true)
                        <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}" title="{{$item->name()}}">
                            <p class="card-header text-label">{{trans('main.code')}}: {{$item->code}}</p>
                        </a>
                    @endif
                    @if($item_find)
                        <div class="card-block">
                            {{-- https://askdev.ru/q/kak-vyzvat-funkciyu-javascript-iz-tega-href-v-html-276225/--}}
                            <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}"
                               title="{{$item->name()}}">
                            @include('view.img',['item'=>$item_find, 'size'=>"medium", 'filenametrue'=>false, 'link'=>false, 'img_fluid'=>true, 'title'=>$item->name()])
                                {{--                                                            @else--}}
                                {{--                                                                <div class="text-danger">--}}
                                {{--                                                                    {{GlobalController::empty_html()}}</div>--}}
                            </a>
                        </div>
                    @endif
                    <div class="card-body">
                        {{--                    <div class="card-footer">--}}
                        <h5 class="card-title ml-3"><a
                                href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}"
                                title="{{$item->name()}}">
                                {{--                            Где $item->name() выходит в cards выводить "<?php echo GlobalController::to_html();?>"--}}
                                <?php echo $item->nmbr();?>
                            </a></h5>
                        {{--                    </div>--}}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col text-center text-label">
                {{trans('main.select_record_for_work')}}
            </div>
        </div>
    @else
        <table class="table table-sm table-bordered table-hover">
            <caption>{{trans('main.select_record_for_work')}}</caption>
            <thead>
            <tr>
                <th class="text-center">#</th>
                @if($base_right['is_list_base_enable'] == true)
                    @if($base->is_code_needed == true)
                        <th class="text-center">{{trans('main.code')}}</th>
                    @endif
                    {{--                Если тип-вычисляемое поле и Показывать Основу с вычисляемым наименованием--}}
                    {{--                или если тип-не вычисляемое наименование--}}
                    {{--            похожая проверка в ext_show.blade.php--}}
                    @if(GlobalController::is_base_calcname_check($base, $base_right))
                        <th @include('layouts.class_from_base',['base'=>$base])>
                            {{trans('main.name')}}</th>
                    @endif
                @endif
                @foreach($links as $link)
                    <?php
                    $base_link_right = GlobalController::base_link_right($link, $role);
                    ?>
                    @if($base_link_right['is_list_link_enable'] == true)
                        {{--                    <th--}}
                        {{--                        @include('layouts.class_from_base',['base'=>$link->parent_base])--}}
                        {{--                    >--}}
                        <th class="text-center">
                            <a href="{{route('item.base_index',['base'=>$link->parent_base_id, 'project'=>$project, 'role'=>$role])}}"
                               title="{{$link->parent_base->names()}}">
                                {{$link->parent_label()}}
                            </a>
                        </th>
                    @endif
                @endforeach
                {{--            <th class="text-center">{{trans('main.user')}}</th>--}}
                {{--            <th class="text-center">{{trans('main.user')}}</th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <?php
                $i++;
                ?>
                <tr>
                    <td class="text-center">
                        {{--                    Не удалять--}}
                        {{--                    <a href="{{route('item.item_index', ['item'=>$item, 'role'=>$role])}}">--}}
                        <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}">
                            {{$i}}
                        </a>
                    </td>
                    @if($base_right['is_list_base_enable'] == true)
                        @if($base->is_code_needed == true)
                            <td class="text-center">
                                <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}">
                                    {{$item->code}}
                                </a>
                            </td>
                        @endif
                        {{--                Если тип-вычисляемое поле и Показывать Основу с вычисляемым наименованием--}}
                        {{--                или если тип-не вычисляемое наименование--}}
                        {{--            похожая проверка в ext_show.blade.php--}}
                        @if(GlobalController::is_base_calcname_check($base, $base_right))
                            <td @include('layouts.class_from_base',['base'=>$base])>
                                @if($base->type_is_image)
                                    @include('view.img',['item'=>$item, 'size'=>"small", 'filenametrue'=>false, 'link'=>true, 'img_fluid'=>false, 'title'=>""])
                                @elseif($base->type_is_document)
                                    @include('view.doc',['item'=>$item])
                                @else
                                    <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}">
                                        {{--                            Где $item->name() выходит в cards выводить "<?php echo GlobalController::to_html();?>"--}}
                                        {{$item->name()}}
                                    </a>
                                @endif
                            </td>
                        @endif
                    @endif
                    {{--                <td class="text-center">&#8594;</td>--}}
                    @foreach($links as $link)
                        <?php
                        $base_link_right = GlobalController::base_link_right($link, $role);
                        ?>
                        @if($base_link_right['is_list_link_enable'] == true)
                            <td
                                @include('layouts.class_from_base',['base'=>$link->parent_base])
                            >
                                <?php
                                $item_find = MainController::view_info($item->id, $link->id);
                                ?>
                                @if($item_find)
                                    @if($link->parent_base->type_is_image())
                                        @include('view.img',['item'=>$item_find, 'size'=>"small", 'filenametrue'=>false, 'link'=>true, 'img_fluid'=>false, 'title'=>""])
                                    @elseif($link->parent_base->type_is_document())
                                        @include('view.doc',['item'=>$item_find])
                                    @else
                                        {{--                                Не удалять: просмотр Пространство--}}
                                        {{--                                                                            проверка, если link - вычисляемое поле--}}
                                        {{--                                    @if ($link->parent_is_parent_related == true || $link->parent_is_numcalc == true)--}}
                                        {{--                                        <a href="{{route('item.item_index', ['item'=>$item_find, 'role'=>$role])}}">--}}
                                        {{--                                            @else--}}
                                        {{--                                                <a href="{{route('item.item_index', ['item'=>$item_find, 'role'=>$role,'par_link'=>$link])}}">--}}
                                        {{--                                                    @endif--}}
                                        {{--                                             Так использовать: 'item'=>$item--}}
                                        <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}">
                                            {{--                            Где $item->name() выходит в cards выводить "<?php echo GlobalController::to_html();?>"--}}
                                            {{$item_find->name()}}
                                        </a>
                                    @endif
                                @else
                                    <div class="text-danger">
                                        {{GlobalController::empty_html()}}</div>
                                @endif
                            </td>
                        @endif
                    @endforeach
                    {{--                    Не удалять--}}
                    {{--                <td>{{$item->created_user_date()}}--}}
                    {{--                </td>--}}
                    {{--                <td>{{$item->updated_user_date()}}--}}
                    {{--                </td>--}}
                    {{--                <td class="text-left">--}}
                    {{--                    <?php--}}
                    {{--                    $link = Link::where('child_base_id', $item->base_id)->exists();--}}
                    {{--                    $main = Main::where('child_item_id', $item->id)->exists();--}}
                    {{--                    ?>--}}
                    {{--                    @if ($link != null)--}}
                    {{--                        @if ($main != null)--}}
                    {{--                            {{trans('main.full')}}--}}
                    {{--                        @endif--}}
                    {{--                    @else--}}
                    {{--                        <span class="text-danger font-weight-bold">{{trans('main.empty')}}</span>--}}
                    {{--                    @endif--}}
                    {{--                </td>--}}
                    {{--                <td class="text-left">--}}
                    {{--                    <?php--}}
                    {{--                    //                  $link = Link::where('parent_base_id', $item->base_id)->first();--}}
                    {{--                    //                  $main = Main::where('parent_item_id', $item->id)->first();--}}
                    {{--                    //                  $link = Link::all()->contains('parent_base_id', $item->base_id);--}}
                    {{--                    //                  $main = Main::all()->contains('parent_item_id', $item->id);--}}
                    {{--                    $link = Link::where('parent_base_id', $item->base_id)->exists();--}}
                    {{--                    $main = Main::where('parent_item_id', $item->id)->exists();--}}
                    {{--                    ?>--}}
                    {{--                    @if ($link != null)--}}
                    {{--                        @if ($main != null)--}}
                    {{--                            {{trans('main.used')}}--}}
                    {{--                        @else--}}
                    {{--                            {{trans('main.not_used')}}--}}
                    {{--                        @endif--}}
                    {{--                    @endif--}}
                    {{--                    /--}}
                    {{--                    @if  (count($item->parent_mains) == 0)--}}
                    {{--                        <b>{{trans('main.not_used')}}</b>--}}
                    {{--                    @else--}}
                    {{--                        {{trans('main.used')}}--}}
                    {{--                    @endif--}}
                    {{--                </td>--}}
                    {{--                Не удалять: другой способ просмотра--}}
                    {{--                <td class="text-center">--}}
                    {{--                    <a href="{{route('main.index_item',$item)}}" title="{{trans('main.information')}}">--}}
                    {{--                        <img src="{{Storage::url('info_record.png')}}" width="15" height="15"--}}
                    {{--                             alt="{{trans('main.info')}}">--}}
                    {{--                    </a>--}}
                    {{--                </td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
    {{$items->links()}}
    {{--    <blockquote class="text-title pt-1 pl-5 pr-5"><?php echo nl2br($project->dc_ext()); ?></blockquote>--}}
    <blockquote class="text-title pt-1 pl-5 pr-5"><?php echo nl2br($project->dc_int()); ?></blockquote>

@endsection


