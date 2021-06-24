@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\GlobalController;
    use App\Http\Controllers\ProjectController;
    $is_num_cancel_all_projects = $is_cancel_all_projects ? 1 : 0;
    ?>
    @include('layouts.project.show_project_role',['project'=>$project, 'role'=>$role])

    <h3 class="display-5">
        {{trans('main.current_status')}}: <span
            class="text-title">{{ProjectController::current_status($project, $role)}}</span>
    </h3>
    <br>
    <p>{{trans('main.user')}}: <b>{{GlobalController::glo_user()->name()}}</b></p>
    <p>{{trans('main.project')}}: <b>{{$project->name()}}</b></p>
    <p>{{trans('main.role')}}: <b>{{$role->name()}}</b></p>
    <hr>
    <p>
    <h4 class="display-5">
        @if ($is_subs == true)
            {{trans('main.send_subscription_request')}}?
        @elseif($is_req_del == true)
            {{trans('main.delete_subscription_request')}}?
        @elseif($is_sb_del == true)
            {{trans('main.delete_subscription')}}?
        @endif
    </h4>
    </p>
    <br>
    <p>
        @if($is_subs==true)
            <button type="button" class="btn btn-dreamer" title="{{trans('main.send')}}"
                    onclick="document.location='{{route('project.subs_create',
                        ['is_request' => 1, 'is_ask' => 0, 'is_cancel_all_projects' => $is_num_cancel_all_projects, 'project'=>$project, 'role'=>$role])}}'">
                <i class="fas fa-paper-plane"></i>
                {{trans('main.send')}}
            </button>
        @endif
        @if($is_req_del==true || $is_sb_del==true)
            <button type="button" class="btn btn-dreamer" title="{{trans('main.delete')}}"
                    onclick="document.location='{{route('project.subs_delete',
                        ['is_ask' => 0, 'is_cancel_all_projects' => $is_num_cancel_all_projects, 'project'=>$project, 'role'=>$role])}}'">
                <i class="fas fa-trash"></i>
                {{trans('main.delete')}}
            </button>
        @endif
        <button type="button" class="btn btn-dreamer"
                title="{{trans('main.cancel')}}"
                onclick="document.location=
                @if($is_cancel_all_projects == true)
                    '{{route('project.all_index')}}'
                @else
                    '{{route('project.start',
                ['project' => $project->id, 'role' => $role->id])}}'
                @endif
                    ">
            <i class="fas fa-arrow-left"></i>
            {{trans('main.cancel')}}
        </button>
    </p>

@endsection
