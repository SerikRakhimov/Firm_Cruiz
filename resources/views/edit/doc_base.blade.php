@if($base->type_is_document())
    <div class="form-group row">
        <div class="col-sm-3 text-right">
            {{--Выберите файл - документ (.xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt)--}}
            <label for="{{$name}}">{{$base->name()}}(.xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt)<span
                    class="text-danger">*</span>
                @if($update)
                    @if($item->img_doc_exist())
                        ({{mb_strtolower(trans('main.now'))}}:<a href="{{Storage::url($item->filename())}}" target="_blank">
                            {{trans('main.open_document')}}
                        </a>)
                    @endif
                @endif
            </label>
        </div>
        <div class="col-sm-4">
            <input type="file"
                   name="{{$name}}" id="{{$id}}"
                   class="@error("$name") is-invalid @enderror"
                   accept=".xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt">
            @error($name)
            <div class="text-danger">
                {{$message}}
            </div>
            @enderror
        </div>
        <div class="col-sm-5-left">
            <label>{{trans('main.explanation_doc')}} ({{mb_strtolower(trans('main.maximum'))}} {{$base->maxfilesize_title_img_doc}})</label>
        </div>
    </div>
@endif

{{--<div class="form-group row">--}}
{{--    <div class="col-sm-3 text-right">--}}
{{--        --}}{{--Выберите файл - документ (.xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt)--}}
{{--        <label for="name_lang_0">{{$base->name()}}(.xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt)<span--}}
{{--                class="text-danger">*</span>--}}
{{--            @if($update)--}}
{{--                @if($item->img_doc_exist())--}}
{{--                    (сейчас:                                                <a--}}
{{--                        href="{{Storage::url($item_image->filename())}}" target="_blank">--}}
{{--                        Открыть документ--}}
{{--                    </a>)--}}
{{--                @endif--}}
{{--            @endif--}}
{{--        </label>--}}
{{--    </div>--}}
{{--    <div class="col-sm-4">--}}
{{--        <input type="file"--}}
{{--               name="name_lang_0" id="name_lang_0"--}}
{{--               class="@error('name_lang_0') is-invalid @enderror"--}}
{{--               accept=".xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt">--}}
{{--        @error('name_lang_0')--}}
{{--        <div class="text-danger">--}}
{{--            {{$message}}--}}
{{--        </div>--}}
{{--        @enderror--}}
{{--    </div>--}}
{{--    <div class="col-sm-5-left">--}}
{{--        <label>Выберите другую картинку для изменения, или оставьте существующую</label>--}}
{{--    </div>--}}
{{--</div>--}}


