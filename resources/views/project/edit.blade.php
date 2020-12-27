@extends('layouts.app')

@section('content')
    <?php
    $update = isset($project);
    ?>
    <p>
        @include('layouts.template.show_name', ['template'=>$template])
    </p>
    <p>
        @include('layouts.edit_title', ['update'=>$update, 'table_name'=>trans('main.project')])
    </p>
    <form action="{{$update ? route('project.update',$project):route('project.store')}}" method="POST"
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
                           value="{{ old('name_lang_' . $key) ?? ($project['name_lang_' . $key] ?? '') }}">
                </div>
                @error('name_lang_' . $key)
                <div class="text-danger">
                    {{$message}}
                </div>
                @enderror
            @endforeach
        </div>

        <div class="form-group row">
            <div class="col-sm-3 text-right">
                <label for="user_id" class="col-form-label">{{trans('main.user')}}<span
                        class="text-danger">*</span></label>
            </div>
            <div class="col-sm-7">
                <select class="form-control"
                        name="user_id"
                        id="user_id"
                        class="@error('user_id') is-invalid @enderror">
                    @foreach ($users as $user)
                        <option value="{{$user->id}}"
                                @if ($update)
                                            "(int) 0" нужно
                                @if ((old('user_id') ?? ($key ?? (int) 0)) ==  $project->user_id)
                                selected
                            @endif
                            @endif
                        >{{$user->name}}, {{$user->email}}</option>
                    @endforeach
                </select>
                @error('user_id')
                <div class="text-danger">
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
                        {{--                    <i class="fa fa-save"></i>--}}
                        {{trans('main.add')}}
                        @else
                            title="{{trans('main.save')}}">
                            {{--                        <i class="fa fa-save"></i>--}}
                            {{trans('main.save')}}
                        @endif
                    </button>
                </div>
                <div class="col-2">
                </div>
                <div class="col-5 text-left">
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.cancel')}}"
                        @include('layouts.project.previous_url')
                    >
                        {{--                    <i class="fa fa-arrow-left"></i>--}}
                        {{trans('main.cancel')}}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
