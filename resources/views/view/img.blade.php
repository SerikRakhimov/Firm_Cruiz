<?php

use \App\Http\Controllers\GlobalController;

?>
@if($item->base->type_is_image())
    @if($item->img_doc_exist())
        @if($filenametrue == true)
            <a href="{{Storage::url($item->filename(true))}}">
                <img src="{{Storage::url($item->filename(true))}}"
                @else
                    <a href="{{Storage::url($item->filename())}}">
                        <img src="{{Storage::url($item->filename())}}"
                             @endif
                             height=
                             @include('types.img.height',['size'=>$size])
                                 alt="" title="{{$item->title_img()}}">
                    </a>
                    @if($item->is_moderation() == true)
                        <div class="text-danger">
                            {{$item->title_img()}}</div>
                    @endif
                    @else
                        <div class="text-danger">
                            {{GlobalController::image_is_missing_html()}}</div>
        @endif
    @endif

