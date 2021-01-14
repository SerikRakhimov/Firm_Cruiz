@extends('layouts.app')

@section('content')
    <?php
    $update = isset($roba);
    $is_role = isset($role);
    $is_base = isset($base);
    ?>
    <p>
        @if($is_role)
            @include('layouts.role.show_name',['role'=>$role])
        @endif
        @if($is_base)
            @include('layouts.base.show_name',['base'=>$base])
        @endif
    </p>
    <p>
        @include('layouts.edit_title', ['update'=>$update, 'table_name'=>trans('main.roba')])
    </p>
    <form action="{{$update ? route('roba.update', $roba):route('roba.store')}}" method="POST"
          enctype=multipart/form-data name="form">
        @csrf

        @if ($update)
            @method('PUT')
        @endif

        @if($is_role)
            <input type="hidden" name="role_id" value="{{$role->id}}">
        @else
            <div class="form-group row">
                <div class="col-sm-3 text-right">
                    <label for="role_id" class="col-form-label">{{trans('main.role')}}<span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-sm-7">
                    <select class="form-control"
                            name="role_id"
                            id="role_id"
                            class="@error('role_id') is-invalid @enderror">
                        @foreach ($roles as $role)
                            <option value="{{$role->id}}"
                                    @if ($update)
                                    @if ((old('role_id') ?? ($roba->role_id ?? (int) 0)) ==  $role->id)
                                    selected
                                @endif
                                @endif
                            >{{$role->name()}}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                    <div class="text-danger">
                        {{$message}}
                    </div>
                    @enderror
                </div>
                <div class="col-sm-2">
                </div>
            </div>
        @endif

        @if($is_base)
            <input type="hidden" name="base_id" value="{{$base->id}}">
        @else
            <div class="form-group row">
                <div class="col-sm-3 text-right">
                    <label for="base_id" class="col-form-label">{{trans('main.base')}}<span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-sm-7">
                    <select class="form-control"
                            name="base_id"
                            id="base_id"
                            class="@error('base_id') is-invalid @enderror">
                        @foreach ($bases as $base)
                            <option value="{{$base->id}}"
                                    @if ($update)
                                    @if ((old('base_id') ?? ($roba->base_id ?? (int) 0)) ==  $base->id)
                                    selected
                                @endif
                                @endif
                            >{{$base->name()}}</option>
                        @endforeach
                    </select>
                    @error('base_id')
                    <div class="text-danger">
                        {{$message}}
                    </div>
                    @enderror
                </div>
                <div class="col-sm-2">
                </div>
            </div>
        @endif

        <div class="form-group row" id="is_create_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_create">{{trans('main.is_create')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_create') is-invalid @enderror"
                       type="checkbox"
                       name="is_create"
                       placeholder=""
                       @if ((old('is_create') ?? ($roba->is_create ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_create')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_read_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_read">{{trans('main.is_read')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_read') is-invalid @enderror"
                       type="checkbox"
                       name="is_read"
                       placeholder=""
                       @if ((old('is_read') ?? ($roba->is_read ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_read')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>


        <div class="form-group row" id="is_update_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_update">{{trans('main.is_update')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_update') is-invalid @enderror"
                       type="checkbox"
                       name="is_update"
                       placeholder=""
                       @if ((old('is_update') ?? ($roba->is_update ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_update')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_delete_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_delete">{{trans('main.is_delete')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_delete') is-invalid @enderror"
                       type="checkbox"
                       name="is_delete"
                       placeholder=""
                       @if ((old('is_delete') ?? ($roba->is_delete ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_delete')
                <div class="invalid-feedback">
                    {{$message}}
                </div>
                @enderror
            </div>
            <div class="col-sm-2">
            </div>
        </div>

        <div class="form-group row" id="is_user_form_group">
            <div class="col-sm-3 text-right">
                <label class="form-label"
                       for="is_user">{{trans('main.is_user')}}</label>
            </div>
            <div class="col-sm-7">
                <input class="@error('is_user') is-invalid @enderror"
                       type="checkbox"
                       name="is_user"
                       placeholder=""
                       @if ((old('is_user') ?? ($roba->is_user ?? false)) ==  true)
                       checked
                    @endif
                >
                @error('is_user')
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
                        <i class="fas fa-save"></i>
                        {{trans('main.add')}}
                        @else
                            title="{{trans('main.save')}}">
                            <i class="fas fa-save"></i>
                            {{trans('main.save')}}
                        @endif
                    </button>
                </div>
                <div class="col-2">
                </div>
                <div class="col-5 text-left">
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.cancel')}}"
                        @include('layouts.roba.previous_url')
                    >
                        <i class="fas fa-arrow-left"></i>
                        {{trans('main.cancel')}}
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection
