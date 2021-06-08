@extends('layouts.app')

@section('content')
    <?php
    use App\Models\Item;
    use App\Http\Controllers\GlobalController;
    // https://ru.coredump.biz/questions/41704091/laravel-file-uploads-failing-when-file-size-is-larger-than-2mb
    //phpinfo(); - для поиска php.ini
    ?>
    @include('layouts.show_project_role',['project'=>$project, 'role'=>$role])
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
                $message = GlobalController::base_maxcount_message($base);
                if ($message != '') {
                    $message = ' (' . $message . ')';
                }
                ?>
                <tr>
                    {{--                                    <th scope="row">{{$i}}</th>--}}
                    <td class="text-center"><h5>
                            <a
                                href="{{route('item.base_index',['base'=>$base, 'project' => $project, 'role' => $role])}}"
                                title="{{$base->names()}}">
                                {{$i}}
                            </a></h5></td>
                    <td class="text-left">
                        <h5>
                            <a
                                href="{{route('item.base_index',['base'=>$base, 'project' => $project, 'role' => $role])}}"
                                title="{{$base->names() . $message}}">
                                {{$base->names()}}
                                {{--                            @auth--}}
                                <span
                                    class="text-muted text-related">
                                    {{GlobalController::items_right($base, $project, $role)['view_count']}}
                                </span>
                                {{--                            @endauth--}}
                            </a>
                        </h5>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    {{$bases->links()}}

    {{--    <h3 class="text-center">Справочники</h3><br>--}}

    {{--    <div class="card-deck">--}}
    {{--        <?php--}}
    {{--        $i = $bases->firstItem() - 1;--}}
    {{--        ?>--}}
    {{--        @foreach($bases as $base)--}}
    {{--            <?php--}}
    {{--            $base_right = GlobalController::base_right($base, $role);--}}
    {{--            ?>--}}
    {{--            @if($base_right['is_list_base_calc'] == true)--}}
    {{--                <?php--}}
    {{--                $i++;--}}
    {{--                ?>--}}
    {{--                <div class="card shadow">--}}
    {{--                    --}}{{--                                        <p class="card-header text-center">{{$i}}</p>--}}
    {{--                    <div class="card-body">--}}
    {{--                        <h5 class="card-title text-center">--}}
    {{--                            <a--}}
    {{--                                href="{{route('item.base_index',['base'=>$base, 'project' => $project, 'role' => $role])}}"--}}
    {{--                                title="{{$base->names()}}">--}}
    {{--                                {{$base->names()}}--}}

    {{--                                <small class="text-related">--}}
    {{--                                    {{GlobalController::items_right($base, $project, $role)['view_count']}}--}}
    {{--                                </small>--}}
    {{--                            </a>--}}
    {{--                        </h5>--}}
    {{--                    </div>--}}
    {{--                    --}}{{--                    <div class="card-footer text-center">--}}
    {{--                    --}}{{--                        <small class="text-muted">--}}
    {{--                    --}}{{--                            {{GlobalController::items_right($base, $project, $role)['view_count']}}--}}
    {{--                    --}}{{--                        </small>--}}
    {{--                    --}}{{--                    </div>--}}
    {{--                </div>--}}
    {{--            @endif--}}
    {{--        @endforeach--}}
    {{--        {{$bases->links()}}--}}

    {{--    </div>--}}

    @if(1==2)
        <?php
        $i = $bases->firstItem() - 1;
        ?>
        <div class="row">
            <div class="col-2">
            </div>
            <div class="col-8">
                <ul class="list-group">
                    @foreach($bases as $base)
                        <?php
                        $base_right = GlobalController::base_right($base, $role);
                        ?>
                        @if($base_right['is_list_base_calc'] == true)
                            <?php
                            $i++;
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center listgroup">
                                <h5 class="card-title text-center">
                                    <a
                                        href="{{route('item.base_index',['base'=>$base, 'project' => $project, 'role' => $role])}}"
                                        title="{{$base->names()}}">
                                        {{$base->names()}}
                                    </a>
                                </h5>
                                <span
                                    class="badge badge-related badge-pill">{{GlobalController::items_right($base, $project, $role)['view_count']}}</span>
                            </li>
                            {{--                    <div class="card-footer text-center">--}}
                            {{--                        <small class="text-muted">--}}
                            {{--                            {{GlobalController::items_right($base, $project, $role)['view_count']}}--}}
                            {{--                        </small>--}}
                            {{--                    </div>--}}
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        {{$bases->links()}}
    @endif

    <blockquote class="text-title pt-1 pl-5 pr-5"><?php echo nl2br($project->dc_ext()); ?></blockquote>
    {{--    <blockquote class="text-title pt-1 pl-5 pr-5"><?php echo nl2br($project->dc_int()); ?></blockquote>--}}
@endsection

