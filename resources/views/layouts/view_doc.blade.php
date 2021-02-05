@if($item->base->type_is_document())
    <a href="{{Storage::url($item->filename())}}" target="_blank"
       alt="" title="{{$item->title_img()}}">
        {{trans('main.open_document')}}
    </a>
@endif

