@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\BaseController;
    use Illuminate\Support\Facades\Request;
    ?>
    <p>
        @include('layouts.show_title', ['type_form'=>$type_form, 'table_name'=>trans('main.template')])
    </p>

    <p>Id: <b>{{$template->id}}</b></p>

    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$template['name_lang_' . $key]}}</b></p>
    @endforeach

    @if ($type_form == 'show')
        <p>
            <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                    onclick="document.location='{{route('template.edit',$template)}}'" title="{{trans('main.edit')}}">
                            <i class="fas fa-edit"></i>
                {{trans('main.edit')}}
            </button>
            <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                    onclick="document.location='{{route('template.delete_question',$template)}}'"
                    title="{{trans('main.delete')}}">
                            <i class="fas fa-trash"></i>
                {{trans('main.delete')}}
            </button>
        </p>
        <p>
{{--            "mb-1 mb-sm-0" нужно чтобы на маленьких экранах кнопки не слипались (margin-botton - 1px) --}}
            <button type="button" class="btn btn-dreamer mb-1 mb-sm-0" title="{{trans('main.bases')}}"
                    onclick="document.location='{{route('base.index', $template)}}'">
                                            <i class="fas fa-scroll"></i>
                {{trans('main.bases')}}
            </button>
            <button type="button" class="btn btn-dreamer  mb-1 mb-sm-0" title="{{trans('main.roles')}}"
                    onclick="document.location='{{route('role.index', $template)}}'">
                <i class="fas fa-user-circle"></i>
                {{trans('main.roles')}}
            </button>
            <button type="button" class="btn btn-dreamer mb-1 mb-sm-0" title="{{trans('main.projects')}}"
                    onclick="document.location='{{route('project.index_template', $template)}}'">
                <i class="fas fa-project-diagram"></i>
                {{trans('main.projects')}}
            </button>


            <button type="button" class="btn btn-dreamer mb-1 mb-sm-0"
                    title="{{trans('main.cancel')}}" @include('layouts.template.previous_url')>
                            <i class="fas fa-arrow-left"></i>
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('template.delete', $template)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.template.previous_url')>
                                    <i class="fas fa-arrow-left"></i>
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
