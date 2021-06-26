@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\GlobalController;
    use App\Http\Controllers\ProjectController;
    $button_submit_text = '';
    if ($is_subs == true) {
        if ($is_request == true) {
            $button_submit_text = trans('main.send');
        } else {
            $button_submit_text = trans('main.subscribe');
        }
    } else {
        $button_submit_text = trans('main.delete');
    }
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
            @if ($is_request == true)
                {{trans('main.send_subscription_request')}}?
            @else
                {{trans('main.subscribe')}}?
            @endif
        @elseif($is_delete == true)
            @if ($is_request == true)
                {{trans('main.delete_subscription_request')}}?
            @else
                {{trans('main.delete_subscription')}}?
            @endif
        @endif
    </h4>
    </p>
    <p>
    <form action="{{route('project.subs_create_form')}}" method=GET" enctype=multipart/form-data>
        @csrf
        <input type="hidden" name="project_id" value="{{$project->id}}">
        <input type="hidden" name="role_id" value="{{$role->id}}">
        <input type="hidden" name="is_subs" value="{{GlobalController::num_is_boolean($is_subs)}}">
        <input type="hidden" name="is_delete" value="{{GlobalController::num_is_boolean($is_delete)}}">
        <input type="hidden" name="is_request" value="{{GlobalController::num_is_boolean($is_request)}}">
        @if($is_subs == true && $is_request == true)
            <div class="form-group">
                <label for="additional_information">{{trans('main.additional_information')}}<span
                        class="text-danger">*</span></label>
                <input type="text"
                       name="additional_information"
                       id="additional_information"
                       class="form-control @error('additional_information') is-invalid @enderror"
                       placeholder=""
                       value="">
            </div>
        @endif
        @if ($is_subs == true || $is_delete == true)
            <button type="submit" class="btn btn-dreamer" title="{{$button_submit_text}}">
                <i class="fas fa-book-open d-inline"></i>
                {{$button_submit_text}}
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
    </form>

    {{--        @if($is_subs==true)--}}
    {{--            <button type="button" class="btn btn-dreamer" title="{{trans('main.send')}}"--}}
    {{--                    onclick="document.location='{{route('project.subs_create',--}}
    {{--                        ['is_request' => 1, 'is_ask' => 0, 'is_cancel_all_projects' => $is_num_cancel_all_projects, 'project'=>$project, 'role'=>$role])}}'">--}}
    {{--                <i class="fas fa-paper-plane"></i>--}}
    {{--                {{trans('main.send')}}--}}
    {{--            </button>--}}
    {{--        @endif--}}
    {{--        @if($is_req_del==true || $is_sb_del==true)--}}
    {{--            <button type="button" class="btn btn-dreamer" title="{{trans('main.delete')}}"--}}
    {{--                    onclick="document.location='{{route('project.subs_delete',--}}
    {{--                        ['is_ask' => 0, 'is_cancel_all_projects' => $is_num_cancel_all_projects, 'project'=>$project, 'role'=>$role])}}'">--}}
    {{--                <i class="fas fa-trash"></i>--}}
    {{--                {{trans('main.delete')}}--}}
    {{--            </button>--}}
    {{--        @endif--}}
    {{--    </p>--}}

@endsection
