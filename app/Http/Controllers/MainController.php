<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Models\Item;
use App\Models\Link;
use App\Models\Main;
use Illuminate\Http\Request;

class MainController extends Controller
{
    protected function rules()
    {
        return [
            'link_id' => 'exists:links,id',
            'child_item_id' => 'exists:items,id',
            'parent_item_id' => 'exists:items,id',
        ];

    }

    function index()
    {
        $mains = Main::orderBy('link_id')->orderBy('child_item_id')->orderBy('parent_item_id');
        return view('main/index', ['mains' => $mains->paginate(60)]);

    }

    function index_item(Item $item)
    {
//        $child_mains = Main::all()->where('child_item_id', $item->id)->sortBy(function ($main) {
//            return $main->link->parent_base->name() . $main->parent_item->name();
//        });

        $child_mains = Main::where('child_item_id', $item->id)->sortBy(function ($main) {
            return $main->link->parent_base->name() . $main->parent_item->name();
        })->get();



        $parent_mains = Main::where('parent_item_id', $item->id)->sortBy(function ($main) {
            return $main->link->child_base->name() . $main->child_item->name();
        })->get();

        return view('main/index_item',
            ['item' => $item, 'child_mains' => $child_mains, 'parent_mains' => $parent_mains]);

    }

    function index_full(Item $item, Link $link)
    {
//        $item = $main->parent_item;
//        $link_head = $main->link;
        $link_head = $link;
        // исключим $link_head->id, он будет выводится в "заголовке"/"шапке" страницы
        $links = $link_head->child_base->child_links->where('id', '!=', $link_head->id);

        $mains = Main::all()->where('parent_item_id', $item->id)->where('link_id', $link_head->id)->sortBy(function ($main) {
            return $main->link->child_base->name() . $main->child_item->name();
        });

        return view('main/index_full',
            ['item' => $item, 'link_head' => $link_head, 'links' => $links, 'mains' => $mains]);
    }

    function store_full(Request $request)
    {
        $item = $request['item'];
        $link = $request['link'];

        return redirect()->route('main.index_full', ['item' => $item, 'link' => $link]);
    }

    function show(Main $main)
    {
        return view('main/show', ['type_form' => 'show', 'main' => $main]);
    }

    function create()
    {
        // исключая вычисляемые поля
        return view('main/edit', ['links' => Link::all()->where('parent_is_parent_related', false)]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $main = new Main($request->except('_token', '_method'));

        $main->link_id = $request->link_id;
        $main->child_item_id = $request->child_item_id;
        $main->parent_item_id = $request->parent_item_id;

        $message_child_base_id = '';
        if (!($main->link->child_base_id == $main->child_item->base_id)) {
            $message_child_base_id = trans('main.base') . ' != ' . $main->link->child_base->name_lang_1;
        }

        $message_parent_base_id = '';
        if (!($main->link->parent_base_id == $main->parent_item->base_id)) {
            $message_parent_base_id = trans('main.base') . ' != ' . $main->link->parent_base->name_lang_1;
        }

        if (($message_child_base_id != '') || ($message_parent_base_id != '')) {
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors(['message_child_base_id' => $message_child_base_id, 'message_parent_base_id' => $message_parent_base_id]);
        }

        $main->save();

        return redirect()->route('main.index');
    }

    function update(Request $request, Main $main)
    {
        // Если данные изменились - выполнить проверку
        if (!(($main->link_id == $request->link_id)
            and ($main->child_item_id == $request->child_item_id)
            and ($main->parent_item_id == $request->parent_item_id))) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $main->fill($data);

        $main->link_id = $request->link_id;
        $main->child_item_id = $request->child_item_id;
        $main->parent_item_id = $request->parent_item_id;

        $message_child_base_id = '';
        if (!($main->link->child_base_id == $main->child_item->base_id)) {
            $message_child_base_id = trans('main.base') . ' != ' . $main->link->child_base->name_lang_1;
        }

        $message_parent_base_id = '';
        if (!($main->link->parent_base_id == $main->parent_item->base_id)) {
            $message_parent_base_id = trans('main.base') . ' != ' . $main->link->parent_base->name_lang_1;
        }

        if (($message_child_base_id != '') || ($message_parent_base_id != '')) {
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors(['message_child_base_id' => $message_child_base_id, 'message_parent_base_id' => $message_parent_base_id]);
        }

        $main->save();

        return redirect()->route('main.index');
    }

    function edit(Main $main)
    {
        // исключая вычисляемые поля
        return view('main/edit', ['main' => $main, 'links' => Link::all()->where('parent_is_parent_related', false)]);
    }

    function delete_question(Main $main)
    {
        return view('main/show', ['type_form' => 'delete_question', 'main' => $main]);
    }

    function delete(Main $main)
    {
        $main->delete();
        return redirect()->route('main.index');
    }

    static function get_parent_item_from_main($child_item_id, $link_id)
    {
        $item = null;
        //$main = Main::all()->where('child_item_id', $child_item_id)->where('link_id', $link_id)->first();
        //$main = Main::where(['child_item_id'=> $child_item_id, 'link_id'=> $link_id])->first();
        //$main = $cursor->where('child_item_id', $child_item_id)->where('link_id', $link_id)->first();
        $main = Main::where('child_item_id', $child_item_id)->where('link_id', $link_id)->first();
        if ($main) {
            $item = $main->parent_item;
        }
        return $item;
    }
    // вывод объекта по имени главного $item и $link
    static function view_info($child_item_id, $link_id)
    {
        $item = null;
        $item_find = Item::find($child_item_id);
        $link_find = Link::find($link_id);
        if ($item_find && $link_find) {
            if ($link_find->parent_is_parent_related == true) {
                $item = ItemController::get_parent_item_from_calc_child_item($item_find, $link_find, true)['result_item'];
            } else {
                $item = self::get_parent_item_from_main($child_item_id, $link_id);
            }
        }
        return $item;
    }

}
