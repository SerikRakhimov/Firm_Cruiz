@extends('layouts.app')

@section('content')
    <?php
    use App\Models\Item;
    use App\Http\Controllers\GlobalController;
    // https://ru.coredump.biz/questions/41704091/laravel-file-uploads-failing-when-file-size-is-larger-than-2mb
    //phpinfo(); - для поиска php.ini
    ?>
{{--    @include('layouts.template.show_name',['template'=>$template])--}}
    {{$project->name()}} - {{$role->name()}}
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
                $base_right = GlobalController::base_right($base, $role);
            ?>
            @if($base_right['is_list_base_calc'] == true)
                <?php
                $i++;
                ?>
                <tr>
{{--                                    <th scope="row">{{$i}}</th>--}}
                    <td class="text-center">{{$i}}</td>
                    <td class="text-left">
                        <a
                            href="{{route('item.base_index',['base'=>$base, 'project' => $project, 'role' => $role])}}"
                           title="{{$base->names()}}">
                            {{$base->names()}}
                            ({{count(GlobalController::items_right($base, $project, $role)['itget'])}})
                        </a>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    {{$bases->links()}}
@endsection

