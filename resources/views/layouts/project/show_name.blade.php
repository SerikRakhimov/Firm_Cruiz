@include('layouts.template.show_name',['template'=>$template])
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <h4><a href={{route('project.show', $project)}}title="{{$project->name()}}">{{$project->name()}}</a>
            </h4>
        </div>
    </div>
</div>

