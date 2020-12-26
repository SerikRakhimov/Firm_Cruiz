@extends('layouts.app')

@section('content')
    <?php
    use App\Http\Controllers\GlobalController;
    ?>
    <p>
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <h3>{{trans('main.users')}}</h3>
            </div>
            <div class="col-2">
            </div>
            <div class="col-5 text-right">
                <button type="button" class="btn btn-dreamer" title="{{trans('main.add')}}"
                        onclick="document.location='{{route('user.create')}}'">
                    {{--                    <i class="fa fa-plus fa-fw d-none d-sm-block "></i>--}}
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
            <th class="text-left">{{trans('main.e-mail')}}</th>
            <th class="text-left">{{trans('main.admin')}}</th>
            <th class="text-center">{{trans('main.projects')}}</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = $users->firstItem() - 1;
        ?>
        @foreach($users as $user)
            <?php
            $i++;
            ?>
            <tr>
                {{--                <th scope="row">{{$i}}</th>--}}
                <td class="text-center">
                    <a href="{{route('user.show',$user)}}" title="{{trans('main.show')}}">
                        {{$i}}
                    </a></td>
                <td class="text-left">
                    <a href="{{route('user.show',$user)}}" title="{{trans('main.show')}}">
                        {{$user->name}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('user.show',$user)}}" title="{{trans('main.show')}}">
                        {{$user->email}}
                    </a>
                </td>
                <td class="text-left">
                    <a href="{{route('user.show',$user)}}" title="{{trans('main.show')}}">
                        {{GlobalController::name_is_boolean($user->is_admin)}}
                    </a>
                </td>
                <td class="text-center">
                    <a href="{{route('project.index', $user)}}" title="{{trans('main.projects')}}">
                        <img src="{{Storage::url('view_record.png')}}" width="15" height="15"
                             alt="{{trans('main.projects')}}">
                    </a>
                </td>

        @endforeach
        </tbody>
    </table>
    {{$users->links()}}
@endsection

