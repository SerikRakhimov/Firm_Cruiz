<?php
use App\Models\Item;
?>
@if($base->type_is_document())
    <div class="form-group row">
        <div class="col-sm-3 text-right">
                {{--Выберите файл - документ (.xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt)--}}
                <label for="{{$name}}">{{$base->name()}}(.xls, .xlsx, .pdf, .doc, .docx,.rtf, .txt)<span
                        class="text-danger">*</span>
                @if($update)
                    @if ($value != null)
                        <?php
                        $item_doc = Item::find($value);
                        ?>
                        @if ($item_doc != null)
                            ({{mb_strtolower(trans('main.now'))}}:<a href="{{Storage::url($item_doc->filename())}}" target="_blank">
                                    {{trans('main.open_document')}}
                            </a>)
                        @endif
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
{{--        <label for="{{$key}}">{{$result['result_parent_label']}}(.xls, .xlsx, .pdf, .doc, .docx,--}}
{{--            .rtf, .txt)<span--}}
{{--                class="text-danger">*</span>--}}
{{--            @if($update)--}}
{{--                @if ($value != null)--}}
{{--                    <?php--}}
{{--                    $item_image = Item::find($value);--}}
{{--                    ?>--}}
{{--                    @if ($item_image != null)--}}
{{--                        (сейчас:--}}
{{--                        <a href="{{Storage::url($item_image->filename())}}" target="_blank">--}}
{{--                            Открыть документ--}}
{{--                        </a>--}}
{{--                        )--}}
{{--                    @endif--}}
{{--                @endif--}}
{{--            @endif--}}
{{--        </label>--}}
{{--    </div>--}}
{{--    <div class="col-sm-4">--}}
{{--        <input type="file"--}}
{{--               name="{{$key}}" id="link{{$key}}"--}}
{{--               class="@error($key) is-invalid @enderror"--}}
{{--               accept=".xls, .xlsx, .pdf, .doc, .docx, .rtf, .txt">--}}
{{--        @error($key)--}}
{{--        <div class="text-danger">--}}
{{--            {{$message}}--}}
{{--        </div>--}}
{{--        @enderror--}}
{{--    </div>--}}
{{--    <div class="col-sm-5-left">--}}
{{--        <label for="{{$key}}">Выберите другую картинку для изменения, или оставьте--}}
{{--            существующую</label>--}}
{{--    </div>--}}
{{--</div>--}}


