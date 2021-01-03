<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-center">
            <h4><a href="{{route('user.index')}}" title="{{trans('main.user')}}" class="text-warning">{{trans('main.user')}}</a><span class="text-warning">:</span>
            <a href={{route('user.show', $user)}}title="{{$user->name()}}">{{$user->name()}}</a>
            </h4>
        </div>
    </div>
</div>

