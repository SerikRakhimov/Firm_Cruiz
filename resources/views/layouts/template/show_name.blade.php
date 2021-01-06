<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <h4><a href="{{route('template.index')}}" title="{{trans('main.template')}}" class="text-warning">{{trans('main.template')}}</a><span class="text-warning">:</span>
                <a href={{route('template.show', $template)}}title="{{$template->name()}}">{{$template->name()}}</a>
            </h4>
        </div>
    </div>
</div>

