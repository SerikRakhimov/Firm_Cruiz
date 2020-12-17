@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\BaseController;
    ?>

    <h3 class="display-5">
        @if ($type_form == 'show')
            {{trans('main.view_record')}}
        @elseif($type_form == 'delete_question')
            {{trans('main.delete_record_question')}}?
        @endif
        <span class="text-info">-</span> <span class="text-success">{{trans('main.template')}}</span>
    </h3>
    <br>

    <p>Id: <b>{{$template->id}}</b></p>

    @foreach (session('glo_menu_save') as $key=>$value)
        <p>{{trans('main.name')}} ({{trans('main.' . $value)}}): <b>{{$template['name_lang_' . $key]}}</b></p>
    @endforeach

    @if ($type_form == 'show')
        <div class="mb-3 btn-group btn-group-sm">
            <a class="btn btn-primary" onclick="javascript:history.back();">{{trans('main.return')}}</a>
        </div>
    @elseif($type_form == 'delete_question')
        <form action="{{route('base.delete', $base)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-primary">{{trans('main.delete')}}</button>
            <a class="btn btn-success" onclick="javascript:history.back();">{{trans('main.cancel')}}</a>
        </form>
    @endif

@endsection
