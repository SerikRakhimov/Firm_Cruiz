@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\BaseController;
    ?>

    <h3 class="display-5">
        @if ($type_form == 'show')
            {{trans('main.viewing_record')}}
        @elseif($type_form == 'delete_question')
            {{trans('main.delete_record_question')}}?
        @endif
        <span class="text-info">-</span> <span class="text-success">{{trans('main.base')}}</span>
    </h3>
    <br>

    <p>Id: <b>{{$base->id}}</b></p>

    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$base['name_lang_' . $key]}}</b></p>
    @endforeach

    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.names')}} ({{trans('main.' . $value)}}): <b>{{$base['names_lang_' . $key]}}</b></p>
    @endforeach

    <p>{{trans('main.type')}}: <b>{{$base->type_name()}}</b></p>
    <p>{{trans('main.is_code_needed')}}: <b>{{$base->is_code_needed}}</b></p>
    <p>{{trans('main.is_code_number')}}: <b>{{$base->is_code_number}}</b></p>
    <p>{{trans('main.is_limit_sign_code')}}: <b>{{$base->is_limit_sign_code}}</b></p>
    <p>{{trans('main.significance_code')}}: <b>{{$base->significance_code}}</b></p>
    <p>{{trans('main.is_code_zeros')}}: <b>{{$base->is_code_zeros}}</b></p>
    <p>{{trans('main.is_suggest_code')}}: <b>{{$base->is_suggest_code}}</b></p>
    <p>{{trans('main.is_suggest_max_code')}}: <b>{{$base->is_suggest_max_code}}</b></p>
    <p>{{trans('main.is_recalc_code')}}: <b>{{$base->is_recalc_code}}</b></p>
    <p>{{trans('main.digits_num')}}: <b>{{$base->digits_num}}</b></p>
    <p>{{trans('main.is_required_lst_num_str')}}: <b>{{$base->name_is_required_lst_num_str()}}</b></p>
    <p>{{trans('main.is_one_value_lst_str')}}: <b>{{$base->name_is_one_value_lst_str()}}</b></p>
    <p>{{trans('main.is_calcname_lst')}}: <b>{{$base->name_is_calcname_lst()}}</b></p>
    <p>{{trans('main.sepa_calcname')}}: <b>{{$base->sepa_calcname}}</b></p>
    <p>{{trans('main.is_same_small_calcname')}}: <b>{{$base->name_is_same_small_calcname()}}</b></p>
    <p>{{trans('main.sepa_same_left_calcname')}}: <b>{{$base->sepa_same_left_calcname}}</b></p>
    <p>{{trans('main.sepa_same_right_calcname')}}: <b>{{$base->name_sepa_same_right_calcname}}</b></p>
    <p>{{trans('main.date_created')}}: <b>{{$base->created_at}}</b></p>
    <p>{{trans('main.date_updated')}}: <b>{{$base->updated_at}}</b></p>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Library</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data</li>
        </ol>
    </nav>

    <?php
    $result = BaseController::form_tree($base->id);
    echo $result;
    ?>

    <?php
    $result = BaseController::get_array_bases_tree_ul($base->id);
    echo $result;
    ?>

    @if ($type_form == 'show')
        <div class="mb-3 btn-group btn-group-sm">
            <a class="btn btn-primary" href="{{ route('base.index') }}">{{trans('main.return')}}</a>
        </div>
    @elseif($type_form == 'delete_question')
        <form action="{{route('base.delete', $base)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-primary">{{trans('main.delete')}}</button>
            <a class="btn btn-success" href="{{ route('base.index') }}">{{trans('main.cancel')}}</a>
        </form>
    @endif

@endsection
