@extends('layouts.app')

@section('content')
    <?php
    Use App\Models\Role;
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <h3>{{$title}}</h3>
            </div>
        </div>
    </div>
    </p>
    <?php
    $i = $projects->firstItem() - 1;
    ?>
    {{--    <div class="card-deck">--}}
    @foreach($projects as $project)
        <?php
        $i++;
        $message = "";
        if ($all_projects == true) {
            $role = Role::where('template_id', $project->template_id)->where('is_default_for_external', true)->first();
            if (!$role) {
                $message = trans('main.role_default_for_external_not_found');
            }
        } else {
            $role = Role::where('template_id', $project->template_id)->where('is_author', true)->first();
            if (!$role) {
                $message = trans('main.role_author_not_found');
            }
        }
        ?>
        <div class="card shadow">
            <img class="card-img-top" src="{{Storage::url('background.png')}}" alt="Card image">
            <p class="card-header">{{$project->template->name()}}</p>
            <div class="card-body">
                <h4 class="card-title">{{$project->name()}}</h4>
                <p class="card-text">{{$project->desc()}}</p>
                @if($role)
                    {{--                ($my_projects ? 1 : 0)--}}
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.start')}}"
                            onclick="document.location='{{route('base.template_index', ['project'=>$project, 'role'=>$role])}}'">
                        <i class="fas fa-play d-inline"></i>
                        {{trans('main.start')}}
                    </button>
                    @else
                    <p class="card-text text-danger">{{$message}}</p>
                @endif
            </div>
            <div class="card-footer">
                <small class="text-muted">{{$project->created_at}}</small>
            </div>
        </div>
        <br>

    @endforeach

    {{--    </div>--}}

    {{--    <div class="card">--}}
    {{--        <h3 class="card-header">Featured</h3>--}}
    {{--        <div class="card-block">--}}
    {{--            <h4 class="card-title">Special title treatment</h4>--}}
    {{--            <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>--}}
    {{--            <a href="#" class="btn btn-primary">Go somewhere</a>--}}
    {{--        </div>--}}
    {{--        <div class="card-footer">--}}
    {{--            <small class="text-muted">{{$project->created_at}}</small>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--        <div class="card bg-primary">--}}
    {{--            <div class="card-body text-center">--}}
    {{--                <p class="card-text">Some text inside the first card</p>--}}
    {{--                <p class="card-text">Some more text to increase the height</p>--}}
    {{--                <p class="card-text">Some more text to increase the height</p>--}}
    {{--                <p class="card-text">Some more text to increase the height</p>--}}
    {{--            </div>--}}
    {{--        </div>--}}

    {{$projects->links()}}
@endsection

