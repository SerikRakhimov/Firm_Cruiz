<?php
use App\Models\Template;
$template = Template::findOrFail($project->template_id);
?>
@include('layouts.template.show_name',['template'=>$template])
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <h4><a href="{{route('project.index_template', $template)}}" title="{{trans('main.project')}}" class="text-warning">{{trans('main.project')}}</a><span class="text-warning">:</span>
            <a href="{{route('project.show_template', $project)}}" title="{{$project->name()}}">{{$project->name()}}</a>
            </h4>
        </div>
    </div>
</div>

