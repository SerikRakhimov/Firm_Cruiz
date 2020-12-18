@extends('layouts.app')

@section('content')
    <?php
    $update = isset($template);
    ?>
    <h3 class="display-5 text-center">
        @if (!$update)
            {{trans('main.new_record')}}
        @else
            {{trans('main.edit_record')}}
        @endif
        <span class="text-info">-</span> <span class="text-success">{{trans('main.template')}}</span>
    </h3>
    <br>

    <form action="{{$update ? route('template.update',$template):route('template.store')}}" method="POST"
          enctype=multipart/form-data>
        @csrf

        @if ($update)
            @method('PUT')
        @endif

        {{--    в единственном числе--}}
        <div class="form-group row">
            @foreach (session('glo_menu_save') as $key=>$value)
                <div class="col-sm-3 text-right">
                    <label for="name_lang_{{$key}}" class="col-form-label">{{trans('main.name')}}
                        ({{trans('main.' . $value)}})<span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-sm-7">
                    <input type="text"
                           name="name_lang_{{$key}}"
                           id="name_lang_{{$key}}"
                           class="form-control @error('name_lang_' . $key) is-invalid @enderror"
                           placeholder=""
                           value="{{ old('name_lang_' . $key) ?? ($template['name_lang_' . $key] ?? '') }}">
                </div>
                @error('name_lang_' . $key)
                <div class="text-danger">
                    {{$message}}
                </div>
                @enderror
            @endforeach
        </div>

        <br>
        <div class="row text-center">
            <div class="col-sm-5 text-right">
                <button type="submit" class="btn btn-primary">
                    @if (!$update)
                        {{trans('main.add')}}
                    @else
                        {{trans('main.save')}}
                    @endif
                </button>
            </div>
            <div class="col-sm-2">
            </div>
            <div class="col-sm-5 text-left">
                <a class="btn btn-success"
                    @include('layouts.previous_url')
                >{{trans('main.cancel')}}</a>
            </div>
        </div>

    </form>
@endsection
