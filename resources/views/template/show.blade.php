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
        <i class="fas fa-download text-primary"></i>
        <button type="button" class="btn btn-gold">
            Download <i class="fas fa-download"></i>
        </button>
        <button type="button" class="btn btn-purple">
            <i class="fas fa-download"></i> Download
        </button>
        <button type="button" class="btn btn-purple">
            <i class="fas fa-plus"></i> Add
        </button>
        <button type="button" class="btn btn-purple">
            <i class="fas fa-edit"></i> Edit
        </button>
        <button type="button" class="btn btn-purple">
            <i class="fas fa-trash"></i> Delete
        </button>
        <button type="button" class="btn btn-purple">
            <i class="fas fa-eye"></i> Show
        </button>
        <button type="button" class="btn btn-purple">
            <i class="fas fa-link"></i> Links
        </button>
        <button type="button" class="btn btn-primary">
            <i class="fas fa-horse"></i> Horse
        </button>
        <div class="mb-3 btn-group btn-group-sm">
            <a class="btn btn-primary"
                @include('layouts.previous_url')
            >{{trans('main.return')}}</a>
        </div>
        <i class="fa fa-spinner fa-pulse fa-3x fa-fw text-success"></i>
        <span class="sr-only">Загрузка...</span>
        <a class="btn btn-danger" href="#">
            <i class="fa fa-trash-o fa-lg"></i> Удалить</a>
        <a class="btn btn-purple" href="#">
            <i class="fa fa-plus"></i> Добавить</a>

        <a class="btn btn-lg btn-success" href="#">
            <i class="fa fa-flag fa-2x pull-left"></i> Font Awesome<br>Версия 4.7.0</a>
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
