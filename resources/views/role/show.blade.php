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
    <p>{{trans('main.is_author')}}: <b>{{GlobalController::name_is_boolean($role->is_author)}}</b></p>
    <p>{{trans('main.is_list_base_sndb')}}: <b>{{GlobalController::name_is_boolean($role->is_list_base_is_sndb)}}</b></p>
    <p>{{trans('main.is_list_base_create')}}: <b>{{GlobalController::name_is_boolean($role->is_list_base_create)}}</b></p>
    <p>{{trans('main.is_list_base_read')}}: <b>{{GlobalController::name_is_boolean($role->is_list_base_read)}}</b></p>
    <p>{{trans('main.is_list_base_update')}}: <b>{{GlobalController::name_is_boolean($role->is_list_base_update)}}</b></p>
    <p>{{trans('main.is_list_base_delete')}}: <b>{{GlobalController::name_is_boolean($role->is_list_base_delete)}}</b></p>
    <p>{{trans('main.is_list_base_byuser')}}: <b>{{GlobalController::name_is_boolean($role->is_list_base_byuser)}}</b></p>
    <p>{{trans('main.is_edit_base_read')}}: <b>{{GlobalController::name_is_boolean($role->is_edit_base_read)}}</b></p>
    <p>{{trans('main.is_edit_base_update')}}: <b>{{GlobalController::name_is_boolean($role->is_edit_base_update)}}</b></p>
    <p>{{trans('main.is_list_link_enable')}}: <b>{{GlobalController::name_is_boolean($role->is_list_link_enable)}}</b></p>
    <p>{{trans('main.is_show_base_enable')}}: <b>{{GlobalController::name_is_boolean($role->is_show_base_enable)}}</b></p>
    <p>{{trans('main.is_show_link_enable')}}: <b>{{GlobalController::name_is_boolean($role->is_show_link_enable)}}</b></p>
    <p>{{trans('main.is_edit_link_read')}}: <b>{{GlobalController::name_is_boolean($role->is_edit_link_read)}}</b></p>
    <p>{{trans('main.is_edit_link_update')}}: <b>{{GlobalController::name_is_boolean($role->is_edit_link_update)}}</b></p>

    @if ($type_form == 'show')
        <p>
            <button class="btn btn-dreamer"
                    onclick="document.location='{{route('role.edit',$role)}}'" title="{{trans('main.edit')}}">
                            <i class="fas fa-edit"></i>
                {{trans('main.edit')}}
            </button>
            <button class="btn btn-dreamer"
                    onclick="document.location='{{route('role.delete_question',$role)}}'"
                    title="{{trans('main.delete')}}">
                            <i class="fas fa-trash"></i>
                {{trans('main.delete')}}
            </button>
        </p>
        <p>
            <button class="btn btn-dreamer" title="{{trans('main.accesses')}}"
                    onclick="document.location='{{route('access.index_role', $role)}}'"
            >
                <i class="fas fa-universal-access"></i>
                {{trans('main.accesses')}}
            </button>
            <button class="btn btn-dreamer" title="{{trans('main.robas')}}"
                    onclick="document.location='{{route('roba.index_role', $role)}}'"
            >
                <i class="fas fa-ring"></i>
                {{trans('main.robas')}}
            </button>
            <button class="btn btn-dreamer"
                    title="{{trans('main.cancel')}}" @include('layouts.role.previous_url')>
                            <i class="fas fa-arrow-left"></i>
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('role.delete', $role)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
                <button class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.role.previous_url')>
                                    <i class="fas fa-arrow-left"></i>
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
