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

        <div class="form-group row">
            @foreach (config('app.locales') as $key=>$value)
                <div class="col-3 text-right">
                    <label for="name_lang_{{$key}}" class="col-form-label">{{trans('main.name')}}
                        ({{trans('main.' . $value)}})<span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-7">
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

        <div class="form-group row">
            @foreach (config('app.locales') as $key=>$value)
                <div class="col-3 text-right">
                    <label for="desc_lang_{{$key}}" class="col-form-label">{{trans('main.desc')}}
                        ({{trans('main.' . $value)}})<span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-7">
                    <textarea
                           name="desc_lang_{{$key}}"
                           id="desc_lang_{{$key}}"
                           rows="5"
                           class="form-control @error('desc_lang_' . $key) is-invalid @enderror"
                           placeholder="">
                        {{ old('desc_lang_' . $key) ?? ($template['desc_lang_' . $key] ?? '') }}
                        </textarea>
                </div>
                @error('desc_lang_' . $key)
                <div class="text-danger">
                    {{$message}}
                </div>
                @enderror
            @endforeach
        </div>

        <br>
        <div class="container-fluid">
            <div class="row text-center">
                <div class="col-5 text-right">
                    <button type="submit" class="btn btn-dreamer"
                            @if (!$update)
{{--                            d-inline нужно, чтобы на маленьких экранах иконка и текст кнопки были на одной линии--}}
{{--                            этот вариант убирает иконку на мобильных телефонах: title="{{trans('main.add')}}"><i class="fas fa-save d-none d-sm-inline"></i>&nbsp;{{trans('main.add')}}--}}
                        title="{{trans('main.add')}}"><i class="fas fa-save d-inline"></i>&nbsp;{{trans('main.add')}}
                        @else
                            title="{{trans('main.save')}}"><i class="fas fa-save d-inline"></i>&nbsp;{{trans('main.save')}}
                        @endif
                    </button>
                </div>
                <div class="col-2">
                </div>
                <div class="col-5 text-left">
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.cancel')}}"
                        @include('layouts.template.previous_url')
                    ><i class="fas fa-arrow-left d-inline"></i>&nbsp;{{trans('main.cancel')}}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
