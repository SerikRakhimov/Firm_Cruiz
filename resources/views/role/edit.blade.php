@extends('layouts.app')

@section('content')
    <?php
    $update = isset($role);
    ?>
    <p>
        @include('layouts.template.show_name', ['template'=>$template])
    </p>
    <p>
        @include('layouts.edit_title', ['update'=>$update, 'table_name'=>trans('main.role')])
    </p>
    <form action="{{$update ? route('role.update',$role):route('role.store')}}" method="POST"
          enctype=multipart/form-data>
        @csrf

        @if ($update)
            @method('PUT')
        @endif
        <input type="hidden" name="template_id" value="{{$template->id}}">

        <div class="form-group row">
            @foreach (session('glo_menu_save') as $key=>$value)
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
                           value="{{ old('name_lang_' . $key) ?? ($role['name_lang_' . $key] ?? '') }}">
                </div>
                @error('name_lang_' . $key)
                <div class="text-danger">
                    {{$message}}
                </div>
                @enderror
            @endforeach
        </div>

        <div class="form-group row" id="is_default_for_external_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_default_for_external">{{trans('main.is_default_for_external')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error($key) is-invalid @enderror"
                       type="checkbox"
                       name="is_default_for_external"
                       placeholder=""
                       @if ((old('is_default_for_external') ?? ($role->is_default_for_external ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_default_for_external')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <br>
        <div class="container-fluid">
            <div class="row text-center">
                <div class="col-5 text-right">
                    <button type="submit" class="btn btn-dreamer"
                            @if (!$update)
                            title="{{trans('main.add')}}">
                        {{--                    <i class="fas fa-save"></i>--}}
                        {{trans('main.add')}}
                        @else
                            title="{{trans('main.save')}}">
                            {{--                        <i class="fas fa-save"></i>--}}
                            {{trans('main.save')}}
                        @endif
                    </button>
                </div>
                <div class="col-2">
                </div>
                <div class="col-5 text-left">
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.cancel')}}"
                        @include('layouts.role.previous_url')
                    >
                        {{--                    <i class="fas fa-arrow-left"></i>--}}
                        {{trans('main.cancel')}}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
