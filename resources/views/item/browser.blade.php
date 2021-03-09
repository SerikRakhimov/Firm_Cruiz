<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    @include('layouts.style_header')
    <title>{{$base->names()}}</title>
</head>
<body>
<p>
<h3 class="display-5 text-center">{{$base->names()}}</h3>
<p>
<form class="navbar-form navbar-right" role="search">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <form class="">
                <div class="row  align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-search h4 text-body"></i>
                    </div>
                    <div class="col">
                        <input class="form-control form-control form-control-borderless" name="search" id="search"
                               type="search"
                               placeholder="{{$sort_by_code == true? trans('main.search_by_code'):trans('main.search_by_name')}} @if($search !="")({{mb_strtolower(trans('main.empty_to_cancel'))}})@endif
                                   ">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success" type="button" onclick="seach_click()">
                            {{trans('main.search')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</form>
<br>
<div class="row justify-content-center">
    @if($search !="")
        {{$save_by_code == true? trans('main.search_by_code'):trans('main.search_by_name')}} "
        <mark>*{{$search}}*</mark>":
        @if(count($items) == 0)
            {{mb_strtolower(trans('main.no_data'))}}!
        @endif
    @endif
</div>

@if(count($items) !=0)
    <table class="table table-sm table-bordered table-hover">
        <caption>{{trans('main.select_record_for_work')}}</caption>
        <thead>
        <th class="text-center {{$sort_by_code == true?'font-italic' : ''}}">
            <a href="{{route('item.browser',['base_id'=>$base->id, 'project_id'=>$project->id, 'role_id'=>$role->id, 'sort_by_code'=>1, 'save_by_code'=>$save_by_code==true?"1":"0", 'search'=>$search])}}"
               title="{{trans('main.sort_by_code')}}">
                {{trans('main.code')}}
            </a></th>
        <th class="text-center {{$sort_by_code == false?'font-italic' : ''}}">
            <a href="{{route('item.browser',['base_id'=>$base->id, 'project_id'=>$project->id, 'role_id'=>$role->id, 'sort_by_code'=>0, 'save_by_code'=>$save_by_code==true?"1":"0", 'search'=>$search])}}"
               title="{{trans('main.sort_by_name')}}">{{trans('main.name')}}</a></th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td class="text-center" style="cursor:pointer"
                    onclick="SelectFile('{{$item->id}}', '{{$item->code}}', '{{$item->name()}}')">{{$item->code}}</td>
                <td class="text-left" style="cursor:pointer"
                    onclick="SelectFile('{{$item->id}}', '{{$item->code}}', '{{$item->name()}}')">{{$item->name()}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$items->links()}}
@endif

<script>
    function seach_click() {
        param = '/{{$sort_by_code == true?"1":"0"}}';
        // " + param + param" правильно
        open('{{route('item.browser', '')}}' + '/' + {{$base->id}} + '/' + {{$project->id}} + '/' + {{$role->id}} + param + param
            + '/' + document.getElementById('search').value, '_self', 'width=800, height=800');
    };

    function SelectFile(id, code, name) {

        opener.item_id.value = id;
        opener.item_code.value = code;
        opener.item_name.innerHTML = name;
        //11111111111111opener.on_parent_refer();
    opener.item_code.dispatchEvent(new Event('change'));

        close();
    }
</script>

</body>
</html>
