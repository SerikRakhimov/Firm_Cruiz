{{--                                если тип корректировки поля - число--}}
@if($base->type_is_number())
    class="text-right"
    {{--                                если тип корректировки поля - дата--}}
    {{--                                если тип корректировки поля - логический--}}
@elseif($base->type_is_date() || $base->type_is_boolean())
    class="text-center"
@else
    class="text-left"
@endif
