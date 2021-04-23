<?php
$is_align_middle = isset($align_middle);
?>
{{--                                если тип корректировки поля - число--}}
@if($base->type_is_number())
    class="text-right"
    {{--                                если тип корректировки поля - дата--}}
    {{--                                если тип корректировки поля - логический--}}
@elseif($base->type_is_date() || $base->type_is_boolean() || $base->type_is_image() || $base->type_is_document())
    {{--    Использовать именно так, как в строках ниже--}}
    @if($is_align_middle)
        class="text-center align-middle"
    @else
        class="text-center"
    @endif
@else
    {{--    Использовать именно так, как в строках ниже--}}
    @if($is_align_middle)
        class="text-left align-middle"
    @else
        class="text-left"
    @endif
@endif
