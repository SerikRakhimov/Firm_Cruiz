<?php
use App\Http\Controllers\GlobalController;
?>
<p>{{trans('main.project')}}: <b>{{$item->project->name()}}</b></p>
<hr>
<h3>{{$item->created_user->name()}}!</h3>
<h3 class="display-5 text-center">{{trans('main.edit_record')}} - {{$item->base->name()}}</h3>
<p>Id: <b>{{$item->id}}</b></p>
Картинка=
<img src="{{Storage::url('edit_record.png')}}">
<img src="https://www.abakusonline.com/storage/4/22/bHeO19ZVuodXxEGNl0K6CEF0gyvRD7tdseVgqcUR.jpeg">
@if($item->base->is_code_needed == true)
    <p>{{trans('main.code')}}: <b>{{$item->code}}</b></p>
@endif
<p>{{trans('main.name')}}:<br><b><?php echo $item->nmbr();?></b></p><br>
<p class="text-label">{{trans('main.updated_user_date_time')}}:
    <b>{{$item->updated_user_date_time()}}</b><br>
</p>
<hr>
<h5>www.abakusonline.com</h5>
