<?php
$is_align_middle = isset($align_middle);
?>
{{--                                если тип корректировки поля - число--}}
@if($base->type_is_number())
    class="text-right"
    {{--                                если тип корректировки поля - дата--}}
    {{--                                если тип корректировки поля - логический--}}
@elseif($base->type_is_date() || $base->type_is_boolean() || $base->type_is_image() || $base->type_is_document())
    class="text-center
    @if($is_align_middle)
        align_middle
    @endif
    "
@else
    class="text-left
    @if($is_align_middle)
        align_middle
    @endif
    "
@endif
