@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\BaseController;
    use Illuminate\Support\Facades\Request;
    use App\User;
    $is_template = isset($template);
    $is_user = isset($user);
    $project_edit = "";
    if($is_template == true){
        $project_edit = "project.edit_template";
    }
    if($is_user == true){
        $project_edit = "project.edit_user";
    }
    ?>
    <p>
        @if($is_template)
            @include('layouts.template.show_name',['template'=>$template])
        @endif
        @if($is_user)
            @include('layouts.user.show_name',['user'=>$user])
        @endif
        @include('layouts.show_title', ['type_form'=>$type_form, 'table_name'=>trans('main.project')])
    </p>

    <p>Id: <b>{{$project->id}}</b></p>

    @if(!$is_template)
        <p>{{trans('main.template')}}: <b>{{$project->template->name()}}</b></p>
    @endif
    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$project['name_lang_' . $key]}}</b></p>
    @endforeach
    @if(!$is_user)
        <p>{{trans('main.user')}}: <b>{{$project->user->name()}}</b></p>
    @endif

    @if ($type_form == 'show')
        <p>
            <button type="button" class="btn btn-dreamer"
                    onclick="document.location='{{route($project_edit,$project)}}'" title="{{trans('main.edit')}}">
                {{--            <i class="fas fa-edit"></i>--}}
                {{trans('main.edit')}}
            </button>
            <button type="button" class="btn btn-dreamer"
                    onclick="document.location='{{route('project.delete_question',$project)}}'"
                    title="{{trans('main.delete')}}">
                {{--            <i class="fas fa-trash"></i>--}}
                {{trans('main.delete')}}
            </button>
        </p>
        <p>
            <button type="button" class="btn btn-dreamer" title="{{trans('main.modules')}}"
                    onclick="document.location='{{route('module.index', $project)}}'">
                {{--            <i class="fas fa-projects"></i>--}}
                {{trans('main.modules')}}
            </button>

            <button type="button" class="btn btn-dreamer"
                    title="{{trans('main.cancel')}}" @include('layouts.project.previous_url')>
                {{--            <i class="fa fa-arrow-left"></i>--}}
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('project.delete', $project)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                    {{--                <i class="fa fa-trash"></i>--}}
                    {{trans('main.delete')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.project.previous_url')>
                    {{--                <i class="fa fa-arrow-left"></i>--}}
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
