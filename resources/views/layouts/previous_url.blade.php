@if (Request::session()->has('previous-url'))
    onclick="document.location='{{session('previous-url')}}'"
@else
    onclick="javascript:history.back();"
@endif
