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
                <span class="badge badge-success">@guest{{trans('main.guest')}}@endguest @auth{{Auth::user()->name()}}@endauth</span>
            </a>
        </div>
        <div class="col-6 text-right">
            <a href="{{route('base.template_index', ['project' => $project, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge badge-success">{{$role->name()}}</span>
            </a>
        </div>
    </div>
    <blockquote class="text-title pt-1 pl-5 pr-5"><?php echo nl2br($role->desc()); ?></blockquote>
</div>
</p>
