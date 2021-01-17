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
                <input class="@error('is_default_for_external') is-invalid @enderror"
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

        <div class="form-group row" id="is_author_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_author">{{trans('main.is_author')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_author') is-invalid @enderror"
                       type="checkbox"
                       name="is_author"
                       placeholder=""
                       @if ((old('is_author') ?? ($role->is_author ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_author')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_list_base_sndb_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_list_base_sndb">{{trans('main.is_list_base_sndb')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_list_base_sndb') is-invalid @enderror"
                       type="checkbox"
                       name="is_list_base_sndb"
                       placeholder=""
                       @if ((old('is_list_base_sndb') ?? ($role->is_list_base_sndb ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_list_base_sndb')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_list_base_create_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_list_base_create">{{trans('main.is_list_base_create')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_list_base_create') is-invalid @enderror"
                       type="checkbox"
                       name="is_list_base_create"
                       placeholder=""
                       @if ((old('is_list_base_create') ?? ($role->is_list_base_create ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_list_base_create')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_list_base_read_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_list_base_read">{{trans('main.is_list_base_read')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_list_base_read') is-invalid @enderror"
                       type="checkbox"
                       name="is_list_base_read"
                       placeholder=""
                       @if ((old('is_list_base_read') ?? ($role->is_list_base_read ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_list_base_read')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>


        <div class="form-group row" id="is_list_base_update_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_list_base_update">{{trans('main.is_list_base_update')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_list_base_update') is-invalid @enderror"
                       type="checkbox"
                       name="is_list_base_update"
                       placeholder=""
                       @if ((old('is_list_base_update') ?? ($role->is_list_base_update ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_list_base_update')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_list_base_delete_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_list_base_delete">{{trans('main.is_list_base_delete')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_list_base_delete') is-invalid @enderror"
                       type="checkbox"
                       name="is_list_base_delete"
                       placeholder=""
                       @if ((old('is_list_base_delete') ?? ($role->is_list_base_delete ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_list_base_delete')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_list_base_byuser_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_list_base_byuser">{{trans('main.is_list_base_byuser')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_list_base_byuser') is-invalid @enderror"
                       type="checkbox"
                       name="is_list_base_byuser"
                       placeholder=""
                       @if ((old('is_list_base_byuser') ?? ($role->is_list_base_byuser ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_list_base_byuser')
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
