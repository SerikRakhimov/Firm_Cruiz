<p>
<div class="container-fluid">
    <div class="row">
        <div class="col-3 text-left">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
{{--                <span class="badge badge-success">{{$project->name()}}</span>--}}
                <span class="badge badge-success">{{Auth::user()->name()}}</span>
            </a>
        </div>
        <div class="col-6 text-center">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge badge-success">{{$project->name()}}</span>
            </a>
        </div>
        <div class="col-3 text-right">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge badge-success">{{$role->name()}}</span>
            </a>
        </div>
    </div>
</div>
</p>
