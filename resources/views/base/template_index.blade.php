@extends('layouts.app')

@section('content')
    <?php
    use App\Models\Item;
    use App\Http\Controllers\GlobalController;
    ?>
    @include('layouts.template.show_name',['template'=>$template])
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.bases')}}</h3>
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
            <th class="text-left">{{trans('main.names')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $bases->firstItem() - 1;
        ?>
        @foreach($bases as $base)
            <?php
            $base_right = GlobalController::base_right($base);
            ?>
            @if($base_right['is_enable'] == true)
                <?php
                $i++;
                $items = Item::where('base_id', $base->id)->where('project_id', GlobalController::glo_project_id());
                if ($base_right['is_byuser'] == true) {
                    $items = $items->where('updated_user_id', GlobalController::glo_user_id());
                }
                $items = $items->get();
                ?>
                <tr>
                    {{--                <th scope="row">{{$i}}</th>--}}
                    <td class="text-center">{{$i}}</td>
                    <td class="text-left">
                        <a href="{{route('item.base_index',$base)}}" title="{{$base->names()}}">
                            {{$base->names()}}
                            ({{count($items)}}
                            )
                        </a>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    {{$bases->links()}}
@endsection

