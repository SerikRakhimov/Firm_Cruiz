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
        <button type="button" class="btn btn-dreamer" onclick="document.location='{{route('template.edit',$template)}}'" title="{{trans('main.edit')}}">
            <i class="fas fa-edit"></i> {{trans('main.edit')}}
        </button>
        <button type="button" class="btn btn-dreamer" onclick="document.location='{{route('template.delete_question',$template)}}'" title="{{trans('main.delete')}}">
            <i class="fas fa-trash"></i> {{trans('main.delete')}}
        </button>
        <button type="button" class="btn btn-dreamer" title="{{trans('main.tasks')}}">
            <i class="fas fa-tasks"></i> {{trans('main.tasks')}}
        </button>
        <button type="button" class="btn btn-dreamer" title="{{trans('main.return')}}" @include('layouts.previous_url')>
            <i class="fa fa-arrow-left"></i> {{trans('main.return')}}
        </button>
    @elseif($type_form == 'delete_question')
        <form action="{{route('template.delete', $template)}}" method="POST" id='delete-form'>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" title="{{trans('main.delete')}}">
                <i class="fa fa-trash"></i> {{trans('main.delete')}}
            </button>
            <button type="button" class="btn btn-dreamer" title="{{trans('main.cancel')}}" @include('layouts.previous_url')>
                <i class="fa fa-arrow-left"></i> {{trans('main.cancel')}}
            </button>
        </form>
    @endif

@endsection
