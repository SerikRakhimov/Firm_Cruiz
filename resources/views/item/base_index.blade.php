@extends('layouts.app')

@section('content')
    <?php
    use App\Models\Link;
    use App\Models\Main;
    use \App\Http\Controllers\GlobalController;
    use \App\Http\Controllers\MainController;
    $links = $base->child_links->sortBy('parent_base_number');
    ?>
    <p>
    <div class="container-fluid">
        <div class="row">
            <div class="col text-left align-top">
                <h3>{{$base->names()}}</h3>
            </div>
        </div>
        @if($base_right['is_create'] == true)
            <div class="col-1 text-left">
                <a href="{{route('item.ext_create', $base->id)}}"
                   title="{{trans('main.add')}}">
                    <img src="{{Storage::url('add_record.png')}}" width="15" height="15" alt="{{trans('main.add')}}">
                </a>
            </div>
        @endif
        {{--        <div class="col-1 text-left">--}}
        {{--            <a href="{{route('item.create')}}" title="{{trans('main.add')}}">--}}
        {{--                <img src="{{Storage::url('add_record.png')}}" width="15" height="15"--}}
        {{--                     alt="{{trans('main.add')}}">--}}
        {{--            </a>--}}
        {{--        </div>--}}
        {{--        <div class="col-1 text-left">--}}
        {{--            <a href="{{route('link.base_index',$base)}}" title="{{trans('main.links')}}">--}}
        {{--                <img src="{{Storage::url('links.png')}}" width="15" height="15" alt="{{trans('main.links')}}">--}}
        {{--            </a>--}}
        {{--        </div>--}}
        @if ($base->is_calcname_lst == true)
            <div class="col-1 text-left">
                <a href="{{route('item.calculate_name',$base)}}" title="{{trans('main.calculate_name')}}">
                    <img src="{{Storage::url('calculate_name.png')}}" width="15" height="15"
                         alt="{{trans('main.calculate_name')}}">
                </a>
            </div>
        @endif
        @if ($base->is_recalc_code == true)
            <div class="col-1 text-left">
                <a href="{{route('item.recalculation_codes',$base)}}" title="{{trans('main.recalculation_codes')}}">
                    <img src="{{Storage::url('recalculation_codes.png')}}" width="15" height="15"
                         alt="{{trans('main.recalculation_codes')}}">
                </a>
            </div>
        @endif
    </div>
    </div>
    </p>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">Id</th>
            @if($base->is_code_needed==true)
                <th class="text-center">{{trans('main.code')}}</th>
            @endif
            <th class="text-left">{{trans('main.name')}}</th>
            <th class="text-center"></th>
            @foreach($links as $link)
                <?php
                $base_link_right = GlobalController::base_link_right($link);
                ?>
                @if($base_link_right['is_enable'] == true)
                    <th>
                        <a href="{{route('item.base_index',$link->parent_base_id)}}"
                           title="{{$link->parent_base->names()}}">
                            {{$link->parent_label()}}
                        </a>
                    </th>
                @endif
            @endforeach
            <th class="text-center">{{trans('main.user')}}</th>
            <th class="text-center"></th>
            <th class="text-center"></th>
            <th class="text-center"></th>
            <th class="text-center"></th>
            @if($base_right['is_update'] == true)
                <th class="text-center"></th>
            @endif
            @if($base_right['is_delete'] == true)
                <th class="text-center"></th>
            @endif
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
                <td class="text-center">{{$i}}</td>
                <td class="text-center">{{$item->id}}</td>
                @if($base->is_code_needed==true)
                    <td class="text-center">
                        <a href="{{route('item.item_index', $item)}}">
                            {{$item->code}}
                        </a>
                    </td>
                @endif
                <td class="text-left">
                    <a href="{{route('item.item_index', $item)}}">
                        {{$item->name()}}
                    </a>
                </td>
                <td class="text-center">&#8594;</td>
                @foreach($links as $link)
                    <?php
                    $base_link_right = GlobalController::base_link_right($link);
                    ?>
                    @if($base_link_right['is_enable'] == true)
                        <?php
                        $item_find = MainController::view_info($item->id, $link->id);
                        ?>
                        @if($item_find)
                            <td>
                                {{--                                проверка, если link - вычисляемое поле--}}
                                @if ($link->parent_is_parent_related == true || $link->parent_is_numcalc == true)
                                    <a href="{{route('item.item_index', ['item'=>$item_find])}}">
                                        @else
                                            <a href="{{route('item.item_index', ['item'=>$item_find,'par_link'=>$link])}}">
                                                @endif
                                                {{$item_find->name()}}
                                            </a>
                            </td>
                        @endif
                    @endif
                @endforeach
                <td>{{$item->updated_user->name()}}
                </td>
                <td class="text-left">
                    <?php
                    //                    $link = Link::where('child_base_id', $item->base_id)->first();
                    //                    $main = Main::where('child_item_id', $item->id)->first();
                    $link = Link::where('child_base_id', $item->base_id)->exists();
                    $main = Main::where('child_item_id', $item->id)->exists();
                    ?>
                    @if ($link != null)
                        @if ($main != null)
                            {{trans('main.full')}}
                        @endif
                    @else
                        <span class="text-danger font-weight-bold">{{trans('main.empty')}}</span>
                    @endif
                </td>
                <td class="text-left">
                    <?php
                    //                  $link = Link::where('parent_base_id', $item->base_id)->first();
                    //                  $main = Main::where('parent_item_id', $item->id)->first();
                    //                  $link = Link::all()->contains('parent_base_id', $item->base_id);
                    //                  $main = Main::all()->contains('parent_item_id', $item->id);
                    $link = Link::where('parent_base_id', $item->base_id)->exists();
                    $main = Main::where('parent_item_id', $item->id)->exists();
                    ?>
                    @if ($link != null)
                        @if ($main != null)
                            {{trans('main.used')}}
                        @else
                            {{trans('main.not_used')}}
                        @endif
                    @endif
                    {{--                        /--}}
                    {{--                        @if  (count($item->parent_mains) == 0)--}}
                    {{--                            <b>{{trans('main.not_used')}}</b>--}}
                    {{--                        @else--}}
                    {{--                            {{trans('main.used')}}--}}
                    {{--                        @endif--}}

                </td>
                <td class="text-center">
                    <a href="{{route('item.ext_show',$item)}}" title="{{trans('main.view')}}">
                        <img src="{{Storage::url('view_record.png')}}" width="15" height="15"
                             alt="{{trans('main.view')}}">
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('main.index_item',$item)}}" title="{{trans('main.information')}}">
                        <img src="{{Storage::url('info_record.png')}}" width="15" height="15"
                             alt="{{trans('main.info')}}">
                    </a>
                </td>
                @if($base_right['is_update'] == true)
                    <td class="text-center">
                        <a href="{{route('item.ext_edit', $item)}}" title="{{trans('main.edit')}}">
                            <img src="{{Storage::url('edit_record.png')}}" width="15" height="15"
                                 alt="{{trans('main.edit')}}">
                        </a>
                    </td>
                @endif
                {{--                <td class="text-center">--}}
                {{--                    <a href="{{route('item.edit',$item)}}" title="{{trans('main.edit')}}">--}}
                {{--                        <img src="{{Storage::url('edit_record.png')}}" width="15" height="15"--}}
                {{--                             alt="{{trans('main.edit')}}">--}}
                {{--                    </a>--}}
                {{--                </td>--}}
                @if($base_right['is_delete'] == true)
                    <td class="text-center">
                        <a href="{{route('item.ext_delete_question',$item)}}" title="{{trans('main.delete')}}">
                            <img src="{{Storage::url('delete_record.png')}}" width="15" height="15"
                                 alt="{{trans('main.delete')}}">
                        </a>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$items->links()}}
@endsection

