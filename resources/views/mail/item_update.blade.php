<p>{{trans('main.project')}}: <b>{{$item->project->name()}}</b></p>
<hr>
<h5>{{$item->user->name()}}!</h5>
<h3 class="display-5 text-center">{{trans('main.edit_record')}} - {{$item->base->name()}}</h3>
<p>Id: <b>{{$item->id}}</b></p>
@if($item->base->is_code_needed == true)
    <p>{{trans('main.code')}}: <b>{{$item->code}}</b></p>
@endif
<p>{{trans('main.name')}}: <b>{{$item->name()}}</b></p><br>
<p class="text-label">{{trans('main.updated_user_date_time')}}:
    <b>{{$item->updated_user_date_time()}}</b><br>
</p>
<hr>
<h5>{{config('app.name', 'Abakus')}}</h5>
