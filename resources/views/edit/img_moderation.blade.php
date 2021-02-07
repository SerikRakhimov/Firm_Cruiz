    <div class="form-group row">
        <div class="col-sm-3 text-right">
            {{--                            Выберите файл - изображение, размером не более 500 Кб--}}
            <label for="{{$name}}">{{$item->base->name()}}<span
                    class="text-danger">*</span></label>
        </div>
        <div class="col-sm-4">
            @if($item->image_exist())
                {{mb_strtolower(trans('main.now'))}}:<a href="{{Storage::url($item->filename(true))}}">
                    <img src="{{Storage::url($item->filename(true))}}"
                         height=@include('types.img.height',['size'=>$size])
                             alt="" title="{{$item->title_img()}}">
                </a>
                {{--                    <a href="{{Storage::url($item->filename(true))}}">--}}
                {{--                        <img src="{{Storage::url($item->filename(true))}}" height="450"--}}
                {{--                             alt="" title="{{$item->title_img()}}">--}}
                {{--                    </a>--}}
                @endif
                </label>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-3 text-right">
            {{--                            Выберите файл - изображение, размером не более 500 Кб--}}
        </div>
        <div class="col-sm-4">
            <input type="file"
                   name="{{$name}}" id="{{$id}}"
                   class="@error("$name") is-invalid @enderror"
                   accept="image/*">
            @error($name)
            <div class="text-danger">
                {{$message}}
            </div>
            @enderror
        </div>
        <div class="col-sm-5-left">
            <label>{{trans('main.explanation_img')}}{{$base->maxfilesize_title_img_doc}}</label>
        </div>
    </div>

    {{--    <div class="form-group row">--}}
    {{--        <div class="col-sm-3 text-right">--}}
    {{--            --}}{{--                            Выберите файл - изображение, размером не более 500 Кб--}}
    {{--            <label for="{{$name}}">{{$base->name()}}<span--}}
    {{--                    class="text-danger">*</span>--}}
    {{--                @if($update)--}}
    {{--                    @if($item->image_exist())--}}
    {{--                        ({{mb_strtolower(trans('main.now'))}}:<a href="{{Storage::url($item->filename())}}">--}}
    {{--                            <img src="{{Storage::url($item->filename())}}" height=@include('types.img.height',['size'=>$size])--}}
    {{--                                 alt="" title="{{$item->title_img()}}">--}}
    {{--                        </a>)--}}
    {{--                    @endif--}}
    {{--                @endif--}}
    {{--            </label>--}}
    {{--        </div>--}}
    {{--        <div class="col-sm-4">--}}
    {{--            <input type="file"--}}
    {{--                   name="{{$name}}" id="{{$id}}"--}}
    {{--                   class="@error("$name") is-invalid @enderror"--}}
    {{--                   accept="image/*">--}}
    {{--            @error($name)--}}
    {{--            <div class="text-danger">--}}
    {{--                {{$message}}--}}
    {{--            </div>--}}
    {{--            @enderror--}}
    {{--        </div>--}}
    {{--        <div class="col-sm-5-left">--}}
    {{--            <label>{{trans('main.explanation_img')}}</label>--}}
    {{--        </div>--}}
    {{--    </div>--}}


