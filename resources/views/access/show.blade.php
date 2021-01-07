@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\BaseController;
    use Illuminate\Support\Facades\Request;
    use App\User;
    $is_access = isset($access);
    $is_project = isset($project);
    $is_user = isset($user);
    $is_role = isset($role);
    $access_edit = "";
    if ($is_project == true) {
        $access_edit = "access.edit_project";
    }
    if ($is_user == true) {
        $access_edit = "access.edit_user";
    }
    if ($is_role == true) {
        $access_edit = "access.edit_role";
    }
    ?>
    <p>
        @if($is_project)
            @include('layouts.project.show_name',['project'=>$project])
        @endif
        @if($is_user)
            @include('layouts.user.show_name',['user'=>$user])
        @endif
        @if($is_role)
            @include('layouts.role.show_name',['role'=>$role])
        @endif
        @include('layouts.show_title', ['type_form'=>$type_form, 'table_name'=>trans('main.access')])
    </p>

    <p>Id: <b>{{$access->id}}</b></p>

    @if(!$is_project)
        <p>{{trans('main.project')}}: <b>{{$access->project->name()}}</b></p>
    @endif
    @if(!$is_user)
        <p>{{trans('main.user')}}: <b>{{$access->user->name()}}</b></p>
    @endif
    @if(!$is_role)
        <p>{{trans('main.role')}}: <b>{{$access->role->name()}}</b></p>
    @endif

    @if ($type_form == 'show')
        @if (Auth::user()->isAdmin() ||(($is_role == false) && ($access->role->is_default_for_external == true)))
            <p>
                <button type="button" class="btn btn-dreamer"
                        onclick="document.location='{{route($access_edit,$access)}}'"
                        title="{{trans('main.edit')}}">
                    <i class="fas fa-edit"></i>
                    {{trans('main.edit')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        onclick="document.location='{{route('access.delete_question',$access)}}'"
                        title="{{trans('main.delete')}}">
                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
            </p>
        @endif
        <p>
            <button type="button" class="btn btn-dreamer"
                    title="{{trans('main.cancel')}}" @include('layouts.access.previous_url')>
                <i class="fas fa-arrow-left"></i>
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('access.delete', $access)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.access.previous_url')>
                    <i class="fas fa-arrow-left"></i>
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
