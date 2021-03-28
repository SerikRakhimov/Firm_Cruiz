<p>{{trans('main.project')}}: <b>{{$item->project->name()}}</b></p>
<hr>
<h5>{{$item->created_user->name()}}!</h5>
<h3 class="display-5 text-center">{{trans('main.delete_record')}} - {{$item->base->name()}}</h3>
<p>Id: <b>{{$item->id}}</b></p>
@if($item->base->is_code_needed == true)
    <p>{{trans('main.code')}}: <b>{{$item->code}}</b></p>
@endif
<p>{{trans('main.name')}}:<br><b><?php echo GlobalController::to_html($item);?></b></p><br>
<p class="text-label">{{trans('main.deleted_user_date_time')}}:
    <b>{{$deleted_user_date_time}}</b><br>
</p>
<hr>
<h5>www.abakusonline.com</h5>
