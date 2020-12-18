@extends('layouts.app')

@section('content')

    <?php
    use App\Http\Controllers\BaseController;
    use Illuminate\Support\Facades\Request;
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
        <p>
            <a href="{{route('template.edit',$template)}}" title="{{trans('main.edit')}}">
                <img src="{{Storage::url('edit_record.png')}}" width="15" height="15" alt="{{trans('main.edit')}}">
            </a>
        </p>
        <p>
            <a href="{{route('template.delete_question',$template)}}" title="{{trans('main.delete')}}">
                <img src="{{Storage::url('delete_record.png')}}" width="15" height="15" alt="{{trans('main.delete')}}">
            </a>
        </p>
        <div class="mb-3 btn-group btn-group-sm">
            <a class="btn btn-primary"
                @include('layouts.previous_url')
            >{{trans('main.return')}}</a>
        </div>
    @elseif($type_form == 'delete_question')
        <form action="{{route('template.delete', $template)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-primary">{{trans('main.delete')}}</button>
            <a class="btn btn-success"
                @include('layouts.previous_url')
            >{{trans('main.cancel')}}</a>
        </form>
    @endif

@endsection
