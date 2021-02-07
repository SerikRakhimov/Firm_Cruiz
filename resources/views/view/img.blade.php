@if($item->base->type_is_photo())
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
    @endif

