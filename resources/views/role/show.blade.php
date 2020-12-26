@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\GlobalController;
    use App\Http\Controllers\BaseController;
    use Illuminate\Support\Facades\Request;
    use App\User;
    ?>
    <p>
        @include('layouts.template.show_name', ['template'=>$template])
        @include('layouts.show_title', ['type_form'=>$type_form, 'table_name'=>trans('main.role')])
    </p>

    <p>Id: <b>{{$role->id}}</b></p>

    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$role['name_lang_' . $key]}}</b></p>
    @endforeach
    <p>{{trans('main.is_default_for_external')}}: <b>{{GlobalController::name_is_boolean($role->is_default_for_external)}}</b></p>

    @if ($type_form == 'show')
        <p>
            <button type="button" class="btn btn-dreamer"
                    onclick="document.location='{{route('role.edit',$role)}}'" title="{{trans('main.edit')}}">
                {{--            <i class="fas fa-edit"></i>--}}
                {{trans('main.edit')}}
            </button>
            <button type="button" class="btn btn-dreamer"
                    onclick="document.location='{{route('role.delete_question',$role)}}'"
                    title="{{trans('main.delete')}}">
                {{--            <i class="fas fa-trash"></i>--}}
                {{trans('main.delete')}}
            </button>
        </p>
        <p>
            <button type="button" class="btn btn-dreamer" title="{{trans('main.modules')}}"
                    onclick="document.location='{{route('module.index', $role)}}'">
                {{--            <i class="fas fa-roles"></i>--}}
                {{trans('main.modules')}}
            </button>

            <button type="button" class="btn btn-dreamer"
                    title="{{trans('main.cancel')}}" @include('layouts.role.previous_url')>
                {{--            <i class="fa fa-arrow-left"></i>--}}
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('role.delete', $role)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                    {{--                <i class="fa fa-trash"></i>--}}
                    {{trans('main.delete')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.role.previous_url')>
                    {{--                <i class="fa fa-arrow-left"></i>--}}
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
