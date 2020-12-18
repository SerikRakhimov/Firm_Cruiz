@if (Request::session()->has('previous-url'))
    href="{{session('previous-url')}}"
@else
    onclick="javascript:history.back();"
@endif
