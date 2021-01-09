@extends('layouts.app')

@section('content')
    {{--<div class="container">--}}
    {{--    <div class="row justify-content-center">--}}
    {{--        <div class="col-md-8">--}}
    {{--            <div class="card">--}}
    {{--                <div class="card-header">Dashboard</div>--}}
    {{--                <div class="card-body">--}}
    {{--                    @if (session('status'))--}}
    {{--                        <div class="alert alert-success" role="alert">--}}
    {{--                            {{ session('status') }}--}}
    {{--                        </div>--}}
    {{--                    @endif--}}

    {{--                    You are logged in!--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--</div>--}}
    <?php
    use App\Models\Access;
    use App\Models\Project;
    use App\Models\Role;
    use App\Models\Template;
    use App\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Validation\Rule;
    use App\Rules\IsUniqueAccess;

    $user = Auth::user();
    $update = isset($access);
    $is_project = isset($project);
    $projects = Access::select('project_id')->where('user_id', $user->id)->groupBy('project_id')->get();
    $roles = Role::all();
    $is_user = isset($user);
    $is_role = isset($role);
    ?>
    <p>
        @if(count($projects)==0)
            <a class="dropdown-item" href="{{route('project.index_user', Auth::user())}}">
                {{trans('main.projects')}}
            </a>
            <a class="dropdown-item" href="{{route('access.index_user', Auth::user())}}">
                {{trans('main.accesses')}}
            </a>
    @endif
    <h3 class="display-5 text-center">
        {{trans('main.project_role_selection')}}
    </h3>
    </p>
    <form action="{{route('home.glo_store')}}" method="POST"
          enctype=multipart/form-data name="form">
        @csrf
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
                            <option value="{{$project->project_id}}"
                            >
                                {{--                            {{$project->name()}}--}}
                                {{Project::findOrFail($project->project_id)->name()}}
                            </option>
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
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-dreamer"
                            title="{{trans('main.login')}}">
                        <i class="fas fa-running"></i>
                        {{trans('main.login')}}
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if ($is_user)
        <script>
            var project_id = form.project_id;
            var role_id = form.role_id;
            var role_id_value = null;

            function project_id_changeOption(first) {
                axios.get('/access/get_roles_options_from_user_project/{{$user->id}}/' + project_id.options[project_id.selectedIndex].value).then(function (res) {
                    // если запуск функции не при загрузке страницы
                    if (first != true) {
                        // сохранить текущие значения
                        var role_id_value = role_id.options[role_id.selectedIndex].value;
                    }

                    if (res.data['result_roles_options'] == "") {
                        role_id.innerHTML = '<option value = "0">{{trans('main.no_information_on')}} "' + res.data['result_child_base_name'] + '"!</option>';
                    } else {
                        // заполнение select
                        role_id.innerHTML = res.data['result_roles_options'];
                    }

                    // только если запуск функции при загрузке страницы
                    if (first == true) {
                        // нужно чтобы при первом вызове формы корректировки записи значения полей соответствовали значениям из базы данных
                        @if ($update)  // при корректировке записи
                        for (let i = 0; i < role_id.length; i++) {
                            // если элемент списка = текущему значению из базы данных
                            if (role_id[i].value == {{$access->role_id}}) {
                                // установить selected на true
                                role_id[i].selected = true;
                            }
                        }
                        @endif
                    } else {
                        // нужно чтобы после обновления списка сохранить текущий выбор если соответствующий(child/parent) base не поменялся (при добавлении/корректировке записи)
                        for (let i = 0; i < role_id.length; i++) {
                            // если элемент списка = предыдущему(текущему) значению из базы данных
                            if (role_id[i].value == role_id_value) {
                                // установить selected на true
                                role_id[i].selected = true;
                            }
                        }
                    }
                });
            }

            project_id.addEventListener("change", project_id_changeOption);

            window.onload = function () {
                project_id_changeOption(true);
            };

        </script>
    @endif
@endsection
