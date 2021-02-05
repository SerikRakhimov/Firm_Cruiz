@if($item->base->type_is_photo())
    <a href="{{Storage::url($item->filename())}}">
        <img src="{{Storage::url($item->filename())}}" height="
            @if($size == "small")
            50
@elseif($size == "big")
            250
            @endif
            "
             alt="" title="{{$item->title_img()}}">
    </a>
@endif

