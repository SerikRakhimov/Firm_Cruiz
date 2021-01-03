@extends('layouts.app')

@section('content')
    <?php
    use App\Http\Controllers\GlobalController;
    ?>
    <p>
    @include('layouts.template.show_name',['template'=>$template])
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.roles')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('role.create', ['template'=>$template])}}'">
                    {{--                    <i class="fas fa-plus fa-fw d-none d-sm-block "></i>--}}
                    {{trans('main.add')}}
                </button>
            </div>
        </div>
    </div>
    </p>
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-left">{{trans('main.name')}}</th>
            <th class="text-left">{{trans('main.default')}}</th>
            <th class="text-center">{{trans('main.accesses')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $roles->firstItem() - 1;
        ?>
        @foreach($roles as $role)
            <?php
            $i++;
            ?>
            <tr>
                <td class="text-center">
                    <a href="{{route('role.show',$role)}}" title="{{trans('main.show')}}">
                        {{$i}}
                    </a></td>
                <td class="text-left">
                    <a href="{{route('role.show',$role)}}" title="{{trans('main.show')}}">
                        {{$role->name()}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('role.show',$role)}}" title="{{trans('main.show')}}">
                        {{GlobalController::name_is_boolean($role->is_default_for_external)}}
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('access.index_role', $role)}}" title="{{trans('main.accesses')}}">
                        <i class="fas fa-user-circle"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$roles->links()}}
@endsection

