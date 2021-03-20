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

    $list = $items;

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
        @if(1==2)
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
    </div>
    </p>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            @if($base_right['is_list_base_enable'] == true)
                @if($base->is_code_needed == true)
                    <th class="text-center">{{trans('main.code')}}</th>
                @endif
                {{--            если тип-вычисляемое поле и показывать вычисляемое поле--}}
                {{--            похожая проверка в ext_show.blade.php--}}
                @if(GlobalController::is_base_calcname_enable($base, $base_right))
                    <th @include('layouts.class_from_base',['base'=>$base])>
                        {{trans('main.name')}}</th>
                @endif
            @endif
            @foreach($links as $link)
                <?php
                $base_link_right = GlobalController::base_link_right($link, $role);
                ?>
                @if($base_link_right['is_list_link_enable'] == true)
                    <th
                        @include('layouts.class_from_base',['base'=>$link->parent_base])
                    >
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
        <?php
        $i = $items->firstItem() - 1;
        ?>
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
{{--                       Если тип-не вычисляемое поле и показывать вычисляемое поле--}}
{{--                       или если тип-не вычисляемое натименование--}}
                    {{--            похожая проверка в ext_show.blade.php--}}
                    @if(GlobalController::is_base_calcname_enable($base, $base_right))
                        <td @include('layouts.class_from_base',['base'=>$base])>
                            @if($base->type_is_image)
                                @include('view.img',['item'=>$item, 'size'=>"small", 'filenametrue'=>false])
                            @elseif($base->type_is_document)
                                @include('view.doc',['item'=>$item])
                            @else
                                <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}">
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
                                    @include('view.img',['item'=>$item_find, 'size'=>"small", 'filenametrue'=>false])
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
                                    <a href="{{route('item.ext_show', ['item'=>$item, 'role'=>$role])}}">
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
    {{$items->links()}}
    <div class="row">
        <div class="col-12 text-center">
            {{$project->desc()}}
        </div>
    </div>
@endsection

