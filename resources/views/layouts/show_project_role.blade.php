<p>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge badge-success">{{$project->name()}}</span>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-6 text-left">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge badge-success">{{Auth::user()->name()}}</span>
            </a>
        </div>
        <div class="col-6 text-right">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge badge-success">{{$role->name()}}</span>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            {{$project->desc()}}
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            {{$role->desc()}}
        </div>
    </div>
</div>
</p>
