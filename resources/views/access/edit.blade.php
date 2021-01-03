@extends('layouts.app')

@section('content')
    <?php
    $update = isset($access);
    $is_project = isset($project);
    $is_user = isset($user);
    $is_role = isset($role);
    ?>
    <p>
        @if($is_project)
            @include('layouts.project.show_name',['project'=>$project])
        @endif
        @if($is_user)
            @include('layouts.user.show_name',['user'=>$user])
        @endif
        @if($is_role)
            @include('layouts.role.show_name',['role'=>$role])
        @endif
    </p>
    <p>
        @include('layouts.edit_title', ['update'=>$update, 'table_name'=>trans('main.access')])
    </p>
    <form action="{{$update ? route('access.update', $access):route('access.store')}}" method="POST"
          enctype=multipart/form-data>
        @csrf

        @if ($update)
            @method('PUT')
        @endif
        @if($is_project)
            <input type="hidden" name="project_id" value="{{$project->id}}">
        @else
            <div class="form-group row">
                <div class="col-sm-3 text-right">
                    <label for="project_id" class="col-form-label">{{trans('main.project')}}<span
                            class="text-danger">*</span></label>
                </div>
                <div class="col-sm-7">
                    <select class="form-control"
                            name="project_id"
                            id="project_id"
                            class="@error('project_id') is-invalid @enderror">
                        @foreach ($projects as $project)
                            <option value="{{$project->id}}"
                                    @if ($update)
                                    @if ((old('project_id') ?? ($access->project_id ?? (int) 0)) ==  $project->id)
                                    selected
                                @endif
                                @endif
                            >{{$project->name()}}</option>
                        @endforeach
                    </select>
                    @error('project_id')
                    <div class="text-danger">
                        {{$message}}
                    </div>
                    @enderror
                </div>
                <div class="col-sm-2">
                </div>
            </div>
        @endif

        @if($is_user)
            <input type="hidden" name="user_id" value="{{$user->id}}">
        @else
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
                                    @if ((old('user_id') ?? ($access->user_id ?? (int) 0)) ==  $user->id)
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
                                    @if ((old('role_id') ?? ($access->role_id ?? (int) 0)) ==  $role->id)
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
                        @include('layouts.access.previous_url')
                    >
                        <i class="fas fa-arrow-left"></i>
                        {{trans('main.cancel')}}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
