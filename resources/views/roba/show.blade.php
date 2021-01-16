@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\GlobalController;
    use App\Http\Controllers\BaseController;
    use Illuminate\Support\Facades\Request;
    use App\User;
    $is_roba = isset($roba);
    $is_role = isset($role);
    $is_base = isset($base);
    $roba_edit = "";
    if ($is_role == true) {
        $roba_edit = "roba.edit_role";
    }
    if ($is_base == true) {
        $roba_edit = "roba.edit_base";
    }
    ?>
    <p>
        @if($is_role)
            @include('layouts.role.show_name',['role'=>$role])
        @endif
            @if($is_base)
                @include('layouts.base.show_name',['base'=>$base])
            @endif
        @include('layouts.show_title', ['type_form'=>$type_form, 'table_name'=>trans('main.roba')])
    </p>

    <p>Id: <b>{{$roba->id}}</b></p>

    @if(!$is_role)
        <p>{{trans('main.role')}}: <b>{{$roba->role->name()}}</b></p>
    @endif
    @if(!$is_base)
        <p>{{trans('main.base')}}: <b>{{$roba->base->name()}}</b></p>
    @endif
    <p>{{trans('main.is_inlist')}}: <b>{{GlobalController::name_is_boolean($roba->is_inlist)}}</b></p>
    <p>{{trans('main.is_create')}}: <b>{{GlobalController::name_is_boolean($roba->is_create)}}</b></p>
    <p>{{trans('main.is_read')}}: <b>{{GlobalController::name_is_boolean($roba->is_read)}}</b></p>
    <p>{{trans('main.is_update')}}: <b>{{GlobalController::name_is_boolean($roba->is_update)}}</b></p>
    <p>{{trans('main.is_delete')}}: <b>{{GlobalController::name_is_boolean($roba->is_delete)}}</b></p>
    <p>{{trans('main.is_byuser')}}: <b>{{GlobalController::name_is_boolean($roba->is_byuser)}}</b></p>

    @if ($type_form == 'show')
{{--        @if (Auth::user()->isAdmin() ||!(($is_user == true) && ($roba->role->is_default_for_external == false)))--}}
            <p>
                <button type="button" class="btn btn-dreamer"
                        onclick="document.location='{{route($roba_edit,$roba)}}'"
                        title="{{trans('main.edit')}}">
                    <i class="fas fa-edit"></i>
                    {{trans('main.edit')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        onclick="document.location='{{route('roba.delete_question',$roba)}}'"
                        title="{{trans('main.delete')}}">
                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
            </p>
{{--        @endif--}}
        <p>
            <button type="button" class="btn btn-dreamer"
                    title="{{trans('main.cancel')}}" @include('layouts.roba.previous_url')>
                <i class="fas fa-arrow-left"></i>
                {{trans('main.cancel')}}
            </button>
        </p>
    @elseif($type_form == 'delete_question')
        <form action="{{route('roba.delete', $roba)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <p>
                <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                    <i class="fas fa-trash"></i>
                    {{trans('main.delete')}}
                </button>
                <button type="button" class="btn btn-dreamer"
                        title="{{trans('main.cancel')}}" @include('layouts.roba.previous_url')>
                    <i class="fas fa-arrow-left"></i>
                    {{trans('main.cancel')}}
                </button>
            </p>
        </form>
    @endif

@endsection
