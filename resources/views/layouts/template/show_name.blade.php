<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <h4>    @if ( Auth::user()->isAdmin()))
                <a href="{{route('template.index')}}" title="{{trans('main.template')}}" class="text-warning">{{trans('main.template')}}:</a>
                @else
                    {{trans('main.template')}}:
                @endif
                <a href={{route('template.show', $template)}}title="{{$template->name()}}">{{$template->name()}}</a>
            </h4>
        </div>
    </div>
</div>

