<p>
<div class="container-fluid">
        <p class="text-center">
            <a href="{{route('project.project', ['project' => $project->id, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge-related">{{$project->name()}}</span>
            </a>
        </p>
    <div class="row">
        <div class="col-6 text-left">
            <a href="{{route('project.project', ['project' => $project->id, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge-related">@guest{{trans('main.guest')}}@endguest @auth{{Auth::user()->name()}}@endauth</span>
            </a>
        </div>
        <div class="col-6 text-right">
            <a href="{{route('project.project', ['project' => $project->id, 'role' => $role])}}" title="{{trans('main.bases')}}">
                <span class="badge-related">{{$role->name()}}</span>
            </a>
        </div>
    </div>
    <blockquote class="text-title pt-1 pl-5 pr-5"><?php echo nl2br($role->desc()); ?></blockquote>
</div>
</p>
