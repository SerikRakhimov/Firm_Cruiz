<?php
use App\Http\Controllers\MainController;
$project = $access->project;
$role = $access->role;
$user= GlobalController::glo_user();
?>
<h3 class="display-5 text-center">{{trans('main.subscription_status_has_changed')}}</h3>
<hr>
<h3 class="display-5">
    {{trans('main.current_status')}}: <span
        class="text-title">{{ProjectController::current_status($project, $role)}}</span>
</h3>
<br>
<p>{{trans('main.user')}}: <b>{{$user->name()}}</b></p>
<p>{{trans('main.project')}}: <b>{{$project->name()}}</b></p>
<p>{{trans('main.role')}}: <b>{{$role->name()}}</b></p>

<p class="text-label">{{trans('main.created_user_date_time')}}:
    <b>{{$access->created_user_date_time()}}</b><br>
</p>
<br>
<hr>
<h5>www.abakusonline.com</h5>
