<?php
use App\Http\Controllers\GlobalController;
$tile_view = $item->base->tile_view($base_right);
$link_image = $tile_view['link'];
$item_find = MainController::view_info($item->id, $link_image->id);
?>
<p>{{trans('main.project')}}: <b>{{$item->project->name()}}</b></p>
<hr>
<h5>{{$item->project->user->name()}}!</h5>
<h3 class="display-5 text-center">{{trans('main.new_record')}} - {{$item->base->name()}}</h3>
<p>Id: <b>{{$item->id}}</b></p>
@if($item->base->is_code_needed == true)
    <p>{{trans('main.code')}}: <b>{{$item->code}}</b></p>
@endif
@if($item_find)
    @include('view.img',['item'=>$item_find, 'size'=>"medium", 'filenametrue'=>false, 'link'=>false, 'img_fluid'=>true, 'title'=>$item->name()])
@endif
<p>{{trans('main.name')}}:<br><b><?php echo $item->nmbr();?></b></p><br>
<p class="text-label">{{trans('main.created_user_date_time')}}:
    <b>{{$item->created_user_date_time()}}</b><br>
</p>
<br>
<hr>
<h5>www.abakusonline.com</h5>
