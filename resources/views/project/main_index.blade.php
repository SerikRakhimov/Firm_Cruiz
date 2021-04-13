@extends('layouts.app')

@section('content')
    <?php
    Use App\Models\Role;
    ?>
    <p>
    <h3 class="display-5 text-center">{{$title}}</h3>
    </p>
    <br>
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
        }
        if ($my_projects == true) {
            $role = Role::where('template_id', $project->template_id)->where('is_author', true)->first();
            if (!$role) {
                $message = trans('main.role_author_not_found');
            }
        }
        ?>
        {{--        <div class="card shadow">--}}
        {{--            <img class="card-img-top" src="{{Storage::url('background.png')}}" alt="Card image">--}}
        {{--            <p class="card-header">{{$project->template->name()}}</p>--}}
        {{--            <div class="card-body">--}}
        {{--                <h4 class="card-title">{{$project->name()}}</h4>--}}
        {{--                <p class="card-title text-label">Id = {{$project->id}}</p>--}}
        {{--                --}}{{--                <p class="card-text">{{$project->desc()}}</p>--}}
        {{--                <p class="card-text"><?php echo nl2br($project->dc_ext()); ?></p>--}}
        {{--                @if($role)--}}
        {{--                    --}}{{--                ($my_projects ? 1 : 0)--}}
        {{--                    <button type="button" class="btn btn-dreamer" title="{{trans('main.start')}}"--}}
        {{--                            onclick="document.location='{{route('base.template_index', ['project'=>$project, 'role'=>$role])}}'">--}}
        {{--                        <i class="fas fa-play d-inline"></i>--}}
        {{--                        {{trans('main.start')}}--}}
        {{--                    </button>--}}
        {{--                @else--}}
        {{--                    <p class="card-text text-danger">{{$message}}</p>--}}
        {{--                @endif--}}
        {{--            </div>--}}
        {{--            <div class="card-footer">--}}
        {{--                <small class="text-muted">{{$project->created_at}}</small>--}}
        {{--            </div>--}}
        {{--        </div>--}}
        @if($role)
            <div class="card shadow">
{{--                <img class="card-img-top" src="{{Storage::url('background.png')}}" alt="Card image">--}}
                <p class="card-header">Id = {{$project->id}}</p>

                <div class="card-block">
                    <p class="card-text ml-3 mt-2"><small class="text-muted">{{$project->template->name()}}</small></p>
                </div>
                <div class="card-body">
                    <h4 class="card-title mb-4">{{$project->name()}}</h4>
                    {{--                <p class="card-text">{{$project->desc()}}</p>--}}
                    <p class="card-text"><?php echo nl2br($project->dc_ext()); ?></p>

                    {{--                ($my_projects ? 1 : 0)--}}
                    <button type="button" class="btn btn-dreamer" title="{{trans('main.start')}}"
                            onclick="document.location='{{route('project.start', ['project'=>$project->id, 'role'=>$role])}}'">
                        <i class="fas fa-play d-inline"></i>
                        {{trans('main.start')}}
                    </button>
                    @if ($all_projects == true)
                           <p class="card-text mt-3">
                               <small class="text-muted">{{$_SERVER['SERVER_NAME']}}/project/start/{{$project->id}} - {{mb_strtolower(trans('main.project_link'))}}</small></p>
                    @endif
                </div>
                <div class="card-footer">
                    <small class="text-muted">{{$project->user->name()}}</small>
                </div>
            </div>
        @else
            <p class="card-text text-danger">{{$message}}</p>
        @endif
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


    {{--    <div class="card mt-4 text-label">--}}
    {{--        <p class="card-header text-label">header</p>--}}
    {{--        <div class="row align-items-center">--}}
    {{--            <div class="col-md-3">--}}
    {{--                <img class="img-fluid" src="{{Storage::url('MyPhoto.jpeg')}}" alt="Card image">--}}
    {{--            </div>--}}
    {{--            <div class="col-md-8">--}}
    {{--                <h4 class="card-title">ttttt</h4>--}}
    {{--                <h2 class="card-title mt-2">Yummi Foods</h2>--}}
    {{--                <p>ghghghghhh hhhhhhhhhh ghghghghghhh eeeeeeeeer bbbbbxbxbxbxbxbxbx eeererrerrr hhhhhhffgfggf</p>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="card-footer text-label">--}}
    {{--            <small class="text-muted">Footer</small>--}}
    {{--        </div>--}}
    {{--    </div>--}}


    {{--    <div class="card-columns">--}}
    {{--        <div class="card">--}}
    {{--            <img class="card-img-top img-fluid" src="{{Storage::url('MyPhoto.jpeg')}}" alt="Card image cap">--}}
    {{--            <div class="card-block">--}}
    {{--                <h4 class="card-title">Card title that wraps to a new line</h4>--}}
    {{--                <p class="card-text">This is a longer card with supporting text below as a natural lead-in to additional--}}
    {{--                    content. This content is a little bit longer.</p>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="card p-3">--}}
    {{--            <blockquote class="card-block card-blockquote">--}}
    {{--                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>--}}
    {{--                <footer>--}}
    {{--                    <small class="text-muted">--}}
    {{--                        Someone famous in <cite title="Source Title">Source Title</cite>--}}
    {{--                    </small>--}}
    {{--                </footer>--}}
    {{--            </blockquote>--}}
    {{--        </div>--}}
    {{--        <div class="card">--}}
    {{--            <img class="card-img-top img-fluid" src="{{Storage::url('MyPhoto.jpeg')}}" alt="Card image cap">--}}
    {{--            <div class="card-block">--}}
    {{--                <h4 class="card-title">Card title</h4>--}}
    {{--                <p class="card-text">This card has supporting text below as a natural lead-in to additional content.</p>--}}
    {{--                <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="card card-inverse card-primary p-3 text-center">--}}
    {{--            <blockquote class="card-blockquote">--}}
    {{--                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat.</p>--}}
    {{--                <footer>--}}
    {{--                    <small>--}}
    {{--                        Someone famous in <cite title="Source Title">Source Title</cite>--}}
    {{--                    </small>--}}
    {{--                </footer>--}}
    {{--            </blockquote>--}}
    {{--        </div>--}}
    {{--        <div class="card text-center">--}}
    {{--            <div class="card-block">--}}
    {{--                <h4 class="card-title">Card title</h4>--}}
    {{--                <p class="card-text">This card has supporting text below as a natural lead-in to additional content.</p>--}}
    {{--                <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="card">--}}
    {{--            <img class="card-img img-fluid" src="{{Storage::url('MyPhoto.jpeg')}}" alt="Card image">--}}
    {{--        </div>--}}
    {{--        <div class="card p-3 text-right">--}}
    {{--            <blockquote class="card-blockquote">--}}
    {{--                <p>11111111111111111111111Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat--}}
    {{--                    a ante.</p>--}}
    {{--                <footer>--}}
    {{--                    <small class="text-muted">--}}
    {{--                        Someone famous in <cite title="Source Title">Source Title</cite>--}}
    {{--                    </small>--}}
    {{--                </footer>--}}
    {{--            </blockquote>--}}
    {{--        </div>--}}
    {{--        <div class="card">--}}
    {{--            <div class="card-block">--}}
    {{--                <h4 class="card-title">Card title</h4>--}}
    {{--                <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional--}}
    {{--                    content. This card has even longer content than the first to show that equal height action.</p>--}}
    {{--                <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}




@endsection

