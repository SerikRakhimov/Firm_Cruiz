@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\GlobalController;
    ?>
    @include('layouts.project.show_project_role',['project'=>$project, 'role'=>$role])
    <h3 class="display-5">
        @if ($is_subs == true)
            {{--            {{trans('main.viewing_record')}}--}}
            Отправить запрос на подписку?
        @elseif($is_req_del == true)
            {{--            {{trans('main.delete_record_question')}}?--}}
            Удалить запрос на подписку?
        @elseif($is_sb_del == true)
            {{--            {{trans('main.delete_record_question')}}?--}}
            Удалить подписку?
        @endif
    </h3>
    <br>
    <p>{{trans('main.project')}}: <b>{{$project->name()}}</b></p>
    <p>{{trans('main.role')}}: <b>{{$role->name()}}</b></p>
    <p>{{trans('main.user')}}: <b>{{GlobalController::glo_user()->name()}}</b></p>
    <br>
    <p>
        <button type="button" class="btn btn-danger" title="{{trans('main.delete')}}"
                onclick="document.location='{{route('project.subs_delete',
                        ['is_ask' => 0, 'is_all_projects' => 1, 'project'=>$project, 'role'=>$role])}}'">
            <i class="fas fa-trash"></i>
            {{trans('main.delete')}}
        </button>
        <button type="button" class="btn btn-dreamer"
                title="{{trans('main.cancel')}}">
            <i class="fas fa-arrow-left"></i>
            {{trans('main.cancel')}}
        </button>
    </p>

@endsection
