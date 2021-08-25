<?php

namespace App\Http\Controllers;

use App\Http\Controllers\GlobalController;
use App\Rules\IsUniqueRoba;
use Illuminate\Support\Facades\App;
use App\User;
use App\Models\Base;
use App\Models\Item;
use App\Models\Link;
use App\Models\Main;
use App\Models\Set;
use App\Models\Project;
use App\Models\Role;
use App\Models\Text;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Rules\IsUniqueItem;

class ItemController extends Controller
{
//    protected function rules(Request $request, $project_id, $base_id)
//    {
////    https://qna.habr.com/q/342501
////    use Illuminate\Validation\Rule;
////
////        public function rules()
////    {
////        $rules = [
////            'name_eng'=>'required|string',
////            'field1' => [
////                'required',
////                Rule::unique('table_name')->where(function ($query) {
////                    $query->where('field2', $this->get('field2'));
////                })
////            ],
////        ];
////
////        return $rules;
////    }
////        return [
////            'base_id' => 'exists:bases,id|unique_with: items, base_id, name_lang_0',
////            'name_lang_0' => ['required', 'max:255', 'unique_with: items, base_id, name_lang_0']
////        ];
//        // exists:table,column
//        // поле должно существовать в заданной таблице базе данных.
//        // 1000 - размер картинки и файла
//        //'name_lang_0' => ['max:1000'] не использовать, т.к. при загрузке изображений и документов мешает
//        return [
//                        'code' => ['required', new IsUniqueItem($request, $project_id, $base_id)],
//        ];
//    }

//    protected function name_lang_boolean_rules()
//    {
//        return [
//            'base_id' => 'exists:bases,id|unique_with: items, base_id, name_lang_0',
//            'name_lang_0' => ['unique_with: items, base_id, name_lang_0']
//        ];
//
//    }

    protected function code_rules(Request $request, $project_id, $base_id)
    {
//        return [
//            'name_lang_0' => ['required', 'max:255']
//        ];
        return [
            'code' => ['required', new IsUniqueItem($request, $project_id, $base_id)],
        ];
    }


//    protected function img_rules($input_img, $maxfilesize)
//    {
////        return [
////            $input_img => ['max:' . $maxfilesize]
////        ];
//        return [
//            '16' => ['max:25']
//        ];
//    }

    protected function name_lang_rules()
    {
        return [
            // 255 - макс.размер строковых полей name_lang_x в items
            'name_lang_0' => ['max:255']
        ];
    }

    // Две переменные $sort_by_code и $save_by_code нужны,
    // для случая: когда установлен фильтр (неважно по коду или по наименованию):
    // можно нажимать на заголовки "Код"/"Наименование" - количество записей на экране то же оставаться должно,
    // меняется только сортировка
    // Использовать знак вопроса "/{base_id?}" (web.php)
    //              равенство null "$base_id = null" (ItemController.php),
    // иначе ошибка в function seach_click() - open('{{route('item.browser', '')}}' ...
    //function browser($link_id, $project_id = null, $role_id = null, $item_id = null, bool $sort_by_code = true, bool $save_by_code = true, $search = "")
//    function browser($link_id, $project_id = null, $role_id = null, $item_id = null, $order_by = null, $filter_by = null, $search = "")
    function browser($link_id, $project_id = null, $role_id = null, $item_id = null, $order_by = null, $filter_by = null, $search = "")
    {
        $link = Link::findOrFail($link_id);
        $base_id = $link->parent_base_id;
        $base = Base::findOrFail($base_id);
        $project = Project::findOrFail($project_id);
        $role = Role::findOrFail($role_id);
        $item = Item::findOrFail($item_id);
        $base_right = GlobalController::base_right($base, $role);
        $name = BaseController::field_name();
        $items = ItemController::get_items_main($base, $project, $role, $link, $item);
        if($order_by == null){
            $order_by = "code";
        }
        if($order_by == ""){
            $order_by = "code";
        }
        if($filter_by == null){
            $filter_by = "code";
        }
        if($filter_by == ""){
            $filter_by = "code";
        }
        //dd($item);
        //dd($base);
        //dd($project);
        //dd($role);
        //dd($items->get());
        //$items = ItemController::get_items_for_link($link, $project, $role)['result_parent_base_items_no_get'];
        //$items = ItemController::get_child_items_from_parent_item($base, $item, $link)['result_items'];
        //$items = self::get_items_for_browse($base, $link, $project, $role, $item);
        //dd($link);
        if ($items != null) {
            if ($order_by) {
                if ($order_by == 'name') {
                    $items = $items->orderBy($name);
                } else {
                    $items = $items->orderBy($order_by);
                }
            }
            //dd($items->get());
            //$search ='';
            //$items = $items->where('items.code', 'LIKE', '%' . $search . '%');
            //dd('search=' . $search);
            if ($filter_by && $search != "") {
                //dd("11111");
                if ($filter_by != "") {
                    if ($filter_by == 'name') {
                        $items = $items->where('items.' . $name, 'LIKE', '%' . $search . '%');
                    } else {
                        $items = $items->where('items.' . $filter_by, 'LIKE', '%' . $search . '%');
                    }
                }
            }
//            if ($sort_by_code == true) {
//                if ($base->is_code_number == true) {
//                    // Сортировка по коду числовому
////                $items = Item::selectRaw("*, code*1  AS code_value")
////                    ->where('base_id', $base_id)->where('project_id', $project->id)->orderBy('code_value');
////                $items = $items->selectRaw("*, code*1  AS code_value")
////                    ->where('base_id', $base_id)->where('project_id', $project->id)->orderBy('code_value');
//                    //$items = $items->where('base_id', $base_id)->where('project_id', $project->id)->orderBy('code');
//                    $items = $items->orderBy('code');
//                } else {
//                    // Сортировка по коду строковому
//                    //$items = Item::where('base_id', $base_id)->where('project_id', $project->id)->orderByRaw(strval('code'));
//                    //$items = $items->where('base_id', $base_id)->where('project_id', $project->id)->orderBy('code');
//                    $items = $items->orderBy('code');
//                }
//            } else {
//                // Сортировка по наименованию
////            $items = Item::where('base_id', $base_id)->where('project_id', $project->id)->orderByRaw(strval($name));
// //               $items = $items->where('base_id', $base_id)->where('project_id', $project->id)->orderBy($name);
//                $items = $items->orderBy($name);
//            }
//        }
//        if ($items != null) {
//            // Такая же проверка и в GlobalController (function items_right()),
//            // в ItemController (function browser(), get_items_for_link(), get_items_ext_edit_for_link())
//            if ($base_right['is_list_base_byuser'] == true) {
//                $items = $items->where('created_user_id', GlobalController::glo_user_id());
//            }
//            if ($search != "") {
//                if ($save_by_code == true) {
//                    $items = $items->where('code', 'LIKE', '%' . $search . '%');
//                } else {
//                    $items = $items->where($name, 'LIKE', '%' . $search . '%');
//                }
//            }
        }
        //dd($items->paginate(30));
        //dd($items->get());
//        $ids = $collection->keys()->toArray();
//
//        $items = Item::whereIn('id', $ids)
//            ->orderBy(\DB::raw("FIELD(id, " . implode(',', $ids) . ")"));
        if ($items) {
//            return view('item/browser', ['link' => $link, 'base' => $base, 'project' => $project, 'role' => $role, 'item' => $item, 'base_right' => $base_right, 'sort_by_code' => $sort_by_code, 'save_by_code' => $save_by_code,
//                'items' => $items->paginate(30), 'search' => $search]);
//            return view('item/browser', ['link' => $link, 'base' => $base, 'project' => $project, 'role' => $role, 'item' => $item, 'base_right' => $base_right, 'order_by' => $order_by, 'filter_by' => $filter_by,
//                'items' => $items->paginate(30), 'search' => $search]);
            return view('item/browser', ['link' => $link, 'base' => $base, 'project' => $project, 'role' => $role, 'item' => $item, 'base_right' => $base_right,
                'order_by' => $order_by, 'filter_by' => $filter_by,
                'items' => $items->paginate(30), 'search' => $search]);
        } else {
            return view('message', ['message' => trans('main.no_data')]);
        }
    }

    static function get_items_for_browse(Base $base, Link $link, Project $project, Role $role, Item $item)
    {
        $items = null;
        $item = null;
        // Фильтрация данных
        if ($item) {
            $items = ItemController::get_child_items_from_parent_item($base, $item, $link)['result_items'];
        } else {
            $items = ItemController::get_items_for_link($link, $project, $role)['result_parent_base_items_no_get'];
        }
        return $items;
    }

//    function index()
//    {
//        $items = null;
//        $index = array_search(App::getLocale(), config('app.locales'));
//        if ($index !== false) {   // '!==' использовать, '!=' не использовать
//            switch ($index) {
//                case 0:
//                    //$items = Item::all()->sortBy('name_lang_0');
//                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_0');
//                    break;
//                case 1:
//                    //$items = Item::all()->sortBy(function($row){return $row->name_lang_1 . $row->name_lang_0;});
//                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_1')->orderBy('name_lang_0');
//                    break;
//                case 2:
//                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_2')->orderBy('name_lang_0');
//                    break;
//                case 3:
//                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_3')->orderBy('name_lang_0');
//                    break;
//            }
//        }
//        session(['links' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
//        return view('item/index', ['items' => $items->paginate(60)]);
//    }

    function base_index(Base $base, Project $project, Role $role)
    {
        if (GlobalController::check_project_user($project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        $links_info = ItemController::links_info($base, $role);
        if ($links_info['error_message'] != "") {
            return view('message', ['message' => $links_info['error_message']]);
        }

        $base_right = GlobalController::base_right($base, $role);

        $items_right = GlobalController::items_right($base, $project, $role);
        $items = $items_right['items'];

        if ($items) {
            session(['base_index_previous_url' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
            return view('item/base_index', ['base_right' => $base_right, 'base' => $base, 'project' => $project, 'role' => $role,
                'items' => $items->paginate(60), 'links_info' => $links_info]);
        } else {
            return view('message', ['message' => trans('main.no_access_for_unregistered_users')]);
        }

    }

    function item_index(Item $item, Role $role, Link $par_link = null)
    {
        if (GlobalController::check_project_user($item->project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

//        $items = null;
//        $index = array_search(App::getLocale(), config('app.locales'));
//        if ($index !== false) {   // '!==' использовать, '!=' не использовать
//            switch ($index) {
//                case 0:
//                    //$items = Item::all()->sortBy('name_lang_0');
//                    $items = Item::orderBy('base_id')->orderBy('name_lang_0');
//                    break;
//                case 1:
//                    //$items = Item::all()->sortBy(function($row){return $row->name_lang_1 . $row->name_lang_0;});
//                    $items = Item::orderBy('base_id')->orderBy('name_lang_1')->orderBy('name_lang_0');
//                    break;
//                case 2:
//                    $items = Item::orderBy('base_id')->orderBy('name_lang_2')->orderBy('name_lang_0');
//                    break;
//                case 3:
//                    $items = Item::orderBy('base_id')->orderBy('name_lang_3')->orderBy('name_lang_0');
//                    break;
//            }
//        }
//        return view('item/item_index', ['base'=>$base, 'items' => $items->where('base_id', $base->id)->paginate(60)]);
        session(['links' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
        return view('item/item_index', ['item' => $item, 'role' => $role, 'par_link' => $par_link]);

    }

    function store_link_change(Request $request)
    {
        $item = Item::find($request['item_id']);
        $role = Role::find($request['role_id']);
        $link = Link::find($request['link_id']);
        return redirect()->route('item.item_index', ['item' => $item, 'role' => $role, 'par_link' => $link]);
    }

    private
    function get_child_links(Base $base)
    {
        // "sortBy('parent_base_number')" обязательно использовать
        return $base->child_links->sortBy('parent_base_number');
    }

    private
    function get_array_calc(Base $base, Item $item = null, $create = false, Link $par_link = null, Item $parent_item = null)  // 'Item $item=null' нужно
    {
        // по настройке links
        $plan_child_links = $this->get_child_links($base);
        if (!$create) {
            // по факту в таблице mains
            // Не использовать команду "$fact_child_mains = $item->child_mains;"
            // - неправильно вытаскивает данные, когда находится внутри транзакции при корректировке записи
            //$fact_child_mains = $item->child_mains;
            //$fact_child_mains = Main::all()->where('child_item_id', $item->id);
            $fact_child_mains = Main::where('child_item_id', $item->id)->get();
        }
        $array_plan = array();
        foreach ($plan_child_links as $key => $link) {
            // добавление или корректировка массива по ключу $link_id
            // заносится null, т.к. это план (настройка от таблицы links)
            $array_plan[$link->id] = null;
        }

        // если main->link_id одинаковый для записей, то берется одно значение(последнее по списку)
        $array_fact = array();
        $array_disabled = array();
        $array_refer = array();
        if ($par_link && $parent_item) {
            if (array_key_exists($par_link->id, $array_plan)) {
                $array_disabled[$par_link->id] = $parent_item->id;
            }
            // вычисление зависимых значений по фильтрируемым полям
            self::par_link_calc_in_array_disabled($plan_child_links, $parent_item, $array_disabled, $par_link);
        }

        if ($create) {
            // если переданы $par_link и $parent_item
            foreach ($array_disabled as $key => $value) {
                if (array_key_exists($key, $array_plan)) {
                    $array_fact[$key] = $array_disabled[$key];
                }
            }
        } else {
            foreach ($fact_child_mains as $key => $main) {
                // добавление или корректировка массива по ключу $link_id
                // заносится $main->parent_item_id (используется в форме extended.edit)
                $array_fact[$main->link_id] = $main->parent_item_id;
            }
        }

// объединяем два массива, главный $array_plan
// он содержит количество записей, как настроено в link
// индекс массива = links->id
// значение массива = null (при создании нового item или если в mains нет записи с таким links->id)
// или mains->parent_item_id (по существующим записям в mains)
        foreach ($array_plan as $key => $value) {
            if (array_key_exists($key, $array_fact)) {
                $array_plan[$key] = $array_fact[$key];
//                $link = Link::findOrFail($key);
//                if($link->parent_base->is_code_needed==true && $link->parent_is_enter_refer==true){
//                    // В "$array_fact[$key]" хранится item_id
//                    $item = Item::findOrFail($array_fact[$key]);
//                    $array_refer[$key]=$item->code;
//                }

            }
        }

        // array_disabled() - список полей, которые будут недоступны для ввода
        // array_refer() - список значений $item->code
        return ['array_calc' => $array_plan, 'array_disabled' => $array_disabled, 'array_refer' => $array_refer];
    }

    private
    function get_array_calc_create(Base $base, Link $par_link = null, Item $parent_item = null)
    {
        return $this->get_array_calc($base, null, true, $par_link, $parent_item);
    }

    private
    function get_array_calc_edit(Item $item, Link $par_link = null, Item $parent_item = null)
    {
        return $this->get_array_calc($item->base, $item, false, $par_link, $parent_item);
    }

    // Рекурсивная функция
    // Вычисление зависимых значений по фильтрируемым полям
    private
    function par_link_calc_in_array_disabled($plan_child_links, $parent_item, &$array_disabled, Link $p_link)
    {
        foreach ($plan_child_links as $key => $link) {
            if ($link->parent_is_child_related == true) {
                if ($link->parent_child_related_start_link_id == $p_link->id) {
                    $link_result = Link::find($link->parent_child_related_result_link_id);
                    if ($link_result) {
                        $item = self::get_parent_item_from_child_item($parent_item, $link_result)['result_item'];
                        $array_disabled[$link->id] = $item->id;
                        // рекурсивный вызов этой же функции, $link передается в функцию
                        self::par_link_calc_in_array_disabled($plan_child_links, $parent_item, $array_disabled, $link);
                    }
                }
            }
        }
    }

    function show(Item $item)
    {
        return view('item/show', ['type_form' => 'show', 'item' => $item]);
    }

    function ext_show(Item $item, Role $role)
    {
        if (GlobalController::check_project_user($item->project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        return view('item/ext_show', ['type_form' => 'show', 'item' => $item, 'role' => $role, 'array_calc' => $this->get_array_calc_edit($item)['array_calc']]);
    }

    function ext_create(Base $base, Project $project, Role $role, $heading = 0, Link $par_link = null, Item $parent_item = null)
        // '$heading = 0' использовать; аналог '$heading = false', в этом случае так /item/ext_create/{base}//
    {
        if (GlobalController::check_project_user($project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        $arrays = $this->get_array_calc_create($base, $par_link, $parent_item);
        $array_calc = $arrays['array_calc'];
        $array_disabled = $arrays['array_disabled'];
        $code_new = $this->calculate_new_code($base, $project);
        // Похожая строка внизу
        $code_uniqid = uniqid($base->id . '_', true);

        //$array_parent_related = GlobalController::get_array_parent_related($base);

        return view('item/ext_edit', ['base' => $base,
            'code_new' => $code_new, 'code_uniqid' => $code_uniqid,
            'heading' => $heading,
            'project' => $project,
            'role' => $role,
            'array_calc' => $array_calc,
            'array_disabled' => $array_disabled,
            'par_link' => $par_link, 'parent_item' => $parent_item]);
    }

    function create()
    {
        return view('item/edit', ['bases' => Base::all()]);
    }

    function ext_store(Request $request, Base $base, Project $project, Role $role, $heading)
    {
        if (GlobalController::check_project_user($project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        //https://webformyself.com/kak-v-php-poluchit-znachenie-checkbox/
        //        if($base->type_is_boolean()){
//            $request->validate($this->name_lang_boolean_rules());
//        }else{
        $request->validate($this->code_rules($request, $project->id, $base->id));
//        }

        // Проверка на $base->maxcount_lst
        // Проверка осуществляется только при добавлении записи
        $message = GlobalController::base_maxcount_validate($project, $base, true);
        if ($message != '') {
            $array_mess['name_lang_0'] = $message;
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        // Проверка полей с типом "текст" на длину текста
        if ($base->type_is_text() && $base->length_txt > 0) {
            $errors = false;
            foreach (config('app.locales') as $lang_key => $lang_value) {
                if (strlen($request['name_lang_' . $lang_key]) > $base->length_txt) {
                    $array_mess['name_lang_' . $lang_key] = trans('main.length_txt_rule') . ' ' . $base->length_txt . '!';
                    $errors = true;
                }
            }
            if ($errors) {
                // повторный вызов формы
                return redirect()->back()
                    ->withInput()
                    ->withErrors($array_mess);
            }
        }

        // Проверка на обязательность ввода наименования
        if ($base->is_required_lst_num_str_txt_img_doc == true && $base->is_calcname_lst == false) {
            // Тип - список, строка или текст
            if ($base->type_is_list() || $base->type_is_string() || $base->type_is_text()) {
                $name_lang_array = array();
                // значения null в ""
                $name_lang_array[0] = isset($request->name_lang_0) ? $request->name_lang_0 : "";
                $name_lang_array[1] = isset($request->name_lang_1) ? $request->name_lang_1 : "";
                $name_lang_array[2] = isset($request->name_lang_2) ? $request->name_lang_2 : "";
                $name_lang_array[3] = isset($request->name_lang_3) ? $request->name_lang_3 : "";
                $errors = false;
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if (($base->is_one_value_lst_str_txt == true && $lang_key == 0) || ($base->is_one_value_lst_str_txt == false)) {
                        // Точное сравнение "$name_lang_array[$i] === ''" используется
                        if ($name_lang_array[$i] === '') {
                            $array_mess['name_lang_' . $i] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                            $errors = true;
                        }
                        $i = $i + 1;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - число
            } elseif ($base->type_is_number()) {
                // значения null в "0"
                $name_lang_0_val = isset($request->name_lang_0) ? $request->name_lang_0 : "0";
                $errors = false;
                // "$value === '0'" использовать для точного сравнения (например, при $link->parent_base->type_is_string())
                if ($name_lang_0_val === '0') {
                    $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                    $errors = true;
                } else {
                    $floatvalue = floatval($name_lang_0_val);
                    if ($floatvalue == 0) {
                        $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                        $errors = true;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - изображение
            } elseif ($base->type_is_image()) {
                $errors = false;
                if (!$request->hasFile('name_lang_0')) {
                    $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                    $errors = true;
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - документ
            } elseif ($base->type_is_document()) {
                $errors = false;
                if (!$request->hasFile('name_lang_0')) {
                    $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                    $errors = true;
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
            }
        }
        // Проверка полей с типом "текст" на наличие запрещенных тегов HTML
        if ($base->type_is_text()) {
            $errors = false;
            foreach (config('app.locales') as $lang_key => $lang_value) {
                $text_html_check = GlobalController::text_html_check($request['name_lang_' . $lang_key]);
                if ($text_html_check['result'] == true) {
                    $array_mess['name_lang_' . $lang_key] = $text_html_check['message'] . '!';
                    $errors = true;
                }
            }
            if ($errors) {
                // повторный вызов формы
                return redirect()->back()
                    ->withInput()
                    ->withErrors($array_mess);
            }
        }

        if ($base->type_is_image() || $base->type_is_document()) {
            if ($request->hasFile('name_lang_0')) {
                $fs = $request->file('name_lang_0')->getSize();
                $mx = $base->maxfilesize_img_doc;
                if ($fs > $mx) {
                    $errors = false;
                    if ($request->file('name_lang_0')->isValid()) {
                        $array_mess['name_lang_0'] = self::filesize_message($fs, $mx);
                        $errors = true;
                    }
                    if ($errors) {
                        // повторный вызов формы
                        return redirect()->back()
                            ->withInput()
                            ->withErrors($array_mess);
                    }
                }
            }
        }

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $item = new Item($request->except('_token', '_method'));
        $item->base_id = $base->id;
        //$project = Project::findOrFail($request->project_id);
        //$role = Role::findOrFail($request->role_id);
        // Похожая проверка в ext_edit.blade.php
//        if ($base->is_code_needed == true && $base->is_code_number == true && $base->is_limit_sign_code == true
//            && $base->is_code_zeros == true && $base->is_code_zeros > 0) {
//            // Дополнить код слева нулями
//            $item->code = str_pad($item->code, $base->significance_code, '0', STR_PAD_LEFT);
//        }

        // нужно по порядку: сначала этот блок
        // значения null в ""
        // у строк могут быть пустые значения, поэтому нужно так: '$item->name_lang_0 = isset($request->name_lang_0) ? $request->name_lang_0 : ""'
        $item->name_lang_0 = isset($request->name_lang_0) ? $request->name_lang_0 : "";
        $item->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $item->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $item->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";
        $item->project_id = $project->id;

        // далее этот блок
        // похожие формулы ниже (в этой же процедуре)

        // тип - логический
        if ($base->type_is_boolean()) {
            $item->name_lang_0 = isset($request->name_lang_0) ? "1" : "0";

            // тип - число
        } elseif ($base->type_is_number()) {
            $item->name_lang_0 = GlobalController::save_number_to_item($base, $request->name_lang_0);

        } // тип - текст
        elseif ($base->type_is_text()) {
            $item->name_lang_0 = GlobalController::itnm_left($request->name_lang_0);
            $item->name_lang_1 = GlobalController::itnm_left($request->name_lang_1);
            $item->name_lang_2 = GlobalController::itnm_left($request->name_lang_2);
            $item->name_lang_3 = GlobalController::itnm_left($request->name_lang_3);
        }

        // затем этот блок (используется "$base")
        if ($base->type_is_number() || $base->type_is_date() || $base->type_is_boolean()) {
            // присваивание полям наименование строкового значение числа/даты
//            foreach (config('app.locales') as $key => $value) {
//                if ($key > 0) {
//                    $item['name_lang_' . $key] = $item->name_lang_0;
//                }
//            }
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }

        $this::save_img_doc($request, $item);

        $excepts = array('_token', 'code', '_method', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3');
        $string_langs = $this->get_child_links($base);
        // Формируется массив $code_names - названия полей кодов
        // Формируется массив $string_names - названия полей наименование
        $code_names = array();
        $string_names = array();
        $i = 0;

        foreach ($string_langs as $key => $link) {
            if ($link->parent_base->type_is_string() || $link->parent_base->type_is_text()) {
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    // начиная со второго(индекс==1) элемента массива языков сохранять
                    if ($i > 0) {
                        // для первого (нулевого) языка $input_name = $key ($link->id)
                        // для последующих языков $input_name = $key . '_' . $lang_key($link->id . '_' . $lang_key);
                        // это же правило используется в ext_edit.blade.php
                        //$string_names[] = $link->id . ($lang_key == 0) ? '' : '_' . $lang_key;  // так не работает, дает '' в результате
                        $string_names[] = ($lang_key == 0) ? $link->id : $link->id . '_' . $lang_key;  // такой вариант работает
                    }
                    $i = $i + 1;
                }
            }
            if ($link->parent_is_enter_refer == true) {
                $code_names[] = 'code' . $link->id;
            }
        }

        // загрузить в $inputs все поля ввода, кроме $excepts, $string_names, $string_codes, array_merge() - функция суммирования двух и более массивов
        $inputs = $request->except(array_merge($excepts, $string_names, $code_names));

        $it_texts = null;
        if ($item->base->type_is_text()) {
            $only = array('name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3');
            $it_texts = $request->only($only);

            foreach ($it_texts as $it_key => $it_text) {
                $it_texts[$it_key] = isset($it_texts[$it_key]) ? $it_texts[$it_key] : "";
            }
        }

        // Проверка существования кода объекта
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            if ($link->parent_base->is_code_needed == true && $link->parent_is_enter_refer == true) {
                $item_needed = Item::find($value);
                if (!$item_needed) {
                    $array_mess['code' . $key] = trans('main.code_not_found') . "!";
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
            }
        }

        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                if ($request->hasFile($link->id)) {
                    $fs = $request->file($link->id)->getSize();
                    $mx = $link->parent_base->maxfilesize_img_doc;
                    if ($fs > $mx) {
                        $errors = false;
                        if ($request->file($link->id)->isValid()) {
                            $array_mess[$link->id] = self::filesize_message($fs, $mx);
                            $errors = true;
                        }

                        if ($errors) {
                            // повторный вызов формы
                            return redirect()->back()
                                ->withInput()
                                ->withErrors($array_mess);
                        }
                    }
                }
            }
        }

// обработка для логических полей
// если при вводе формы пометка checkbox не установлена, в $request записи про элемент checkbox вообще нет
// если при вводе формы пометка checkbox установлена, в $request есть запись со значеним "on"
// см. https://webformyself.com/kak-v-php-poluchit-znachenie-checkbox/
//        foreach ($string_langs as $link) {
//            // Проверка нужна
//            $base_link_right = GlobalController::base_link_right($link, $role);
//            if ($base_link_right['is_edit_link_enable'] == false) {
//                continue;
//            }
//            // похожая формула выше (в этой же процедуре)
//            if ($link->parent_base->type_is_boolean()) {
//                // у этой команды два предназначения:
//                // 1) заменить "on" на "1" при отмеченном checkbox
//                // 2) создать новый ([$link->id]-й) элемент массива со значением "0" при выключенном checkbox
//                // в базе данных информация хранится как "0" или "1"
//                $inputs[$link->id] = isset($inputs[$link->id]) ? "1" : "0";
//            }
//        }

        foreach ($string_langs as $link) {
            if ($link->parent_base->type_is_boolean()) {
                // Проверка нужна
                $base_link_right = GlobalController::base_link_right($link, $role);
                if ($base_link_right['is_edit_link_update'] == false) {
                    continue;
                }
                // похожая формула выше (в этой же процедуре)
                // у этой команды два предназначения:
                // 1) заменить "on" на "1" при отмеченном checkbox
                // 2) создать новый ([$link->id]-й) элемент массива со значением "0" при выключенном checkbox
                // в базе данных информация хранится как "0" или "1"
                $inputs[$link->id] = isset($inputs[$link->id]) ? "1" : "0";
            }
        }

        $array_mess = array();
        foreach ($string_langs as $link) {
            if ($link->parent_is_parent_related == false) {
                // Тип - изображение
                if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                    // Проверка на обязательность ввода
                    if ($link->parent_base->is_required_lst_num_str_txt_img_doc == true) {
                        $errors = false;
                        if (!$request->hasFile($link->id)) {
                            $array_mess[$link->id] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                            $errors = true;
                        }
                        if ($errors) {
                            // повторный вызов формы
                            return redirect()->back()
                                ->withInput()
                                ->withErrors($array_mess);
                        }
                    }
                }
            }
        }

        foreach ($inputs as $key => $value) {
            $inputs[$key] = ($value != null) ? $value : "";
        }
        $strings_inputs = $request->only($string_names);

        foreach ($strings_inputs as $key => $value) {
            $strings_inputs[$key] = ($value != null) ? $value : "";
        }

        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                $path = "";
                if ($request->hasFile($key)) {
                    $path = $request[$key]->store('public/' . $item->project_id . '/' . $link->parent_base_id);
                }
                $inputs[$key] = $path;
            } elseif ($link->parent_base->type_is_number()) {
                $inputs[$key] = GlobalController::save_number_to_item($link->parent_base, $value);
            }
        }

        $keys = array_keys($inputs);
        $values = array_values($inputs);

// Проверка полей с типом "текст" на длину текста
        $errors = false;
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            $work_base = $link->parent_base;
            if ($work_base->type_is_text() && $work_base->length_txt > 0) {
                $errors = false;
                $name_lang_value = null;
                $name_lang_key = null;
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if (($work_base->is_one_value_lst_str_txt == true && $lang_key == 0) || ($work_base->is_one_value_lst_str_txt == false)) {
                        if ($i == 0) {
                            $name_lang_key = $key;
                            $name_lang_value = $value;
                        }
                        // начиная со второго(индекс==1) элемента массива языков учитывать
                        if ($i > 0) {
                            $name_lang_key = $key . '_' . $lang_key;
                            $name_lang_value = $strings_inputs[$name_lang_key];
                        }
                        if (strlen($name_lang_value) > $work_base->length_txt) {
                            $array_mess[$name_lang_key] = trans('main.length_txt_rule') . ' ' . $work_base->length_txt . '!';
                            $errors = true;
                        }
                        $i = $i + 1;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
            }
        }

        $errors = false;
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);

            $work_base = $link->parent_base;
            // при типе "логический" проверять на обязательность заполнения не нужно
            $control_required = false;
            // Тип - список
            if ($work_base->type_is_list()) {
                // так не использовать
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str_txt_img_doc == true) {
                    $control_required = true;
                }
                // это правильно

                //$control_required = true;

            } // Тип - число
            elseif ($work_base->type_is_number()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str_txt_img_doc == true) {
                    $control_required = true;
                }
            } // Тип - строка или текст
            elseif ($work_base->type_is_string() || $work_base->type_is_text()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str_txt_img_doc == true) {
                    $control_required = true;
                }
            } // Тип - дата
            elseif ($work_base->type_is_date()) {
                $control_required = true;
            }

            // при типе корректировки поля "строка", "логический" проверять на обязательность заполнения не нужно
            if ($control_required == true) {
                // Тип - строка или текст
                if ($work_base->type_is_string() || $work_base->type_is_text()) {
                    // поиск в таблице items значение с таким же названием и base_id
                    $name_lang_value = null;
                    $name_lang_key = null;
                    $i = 0;
                    foreach (config('app.locales') as $lang_key => $lang_value) {
                        if (($work_base->is_one_value_lst_str_txt == true && $lang_key == 0) || ($work_base->is_one_value_lst_str_txt == false)) {
                            if ($i == 0) {
                                $name_lang_key = $key;
                                $name_lang_value = $value;
                            }
                            // начиная со второго(индекс==1) элемента массива языков учитывать
                            if ($i > 0) {
                                $name_lang_key = $key . '_' . $lang_key;
                                $name_lang_value = $strings_inputs[$name_lang_key];
                            }
                            // "<option value = '0'>" присваивается при заполнении 'edit.blade' если нет данных (объектов по заданному base)            if ($value == 0)
                            // "$value === '0'" использовать для точного сравнения (например, при $link->parent_base->type_is_string())
                            // Преобразование null в '' было ранее произведено
                            if ($name_lang_value == "") {
                                $array_mess[$name_lang_key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                                $errors = true;
                            }
                            $i = $i + 1;
                        }
                    }
                } else {
                    // "<option value = '0'>" присваивается при заполнении 'edit.blade' если нет данных (объектов по заданному base)            if ($value == 0)
                    // "$value === '0'" использовать для точного сравнения (например, при $link->parent_base->type_is_string())
                    if ($value == null) {
                        $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                        $errors = true;
                    } elseif ($value === '0') {
                        $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                        $errors = true;
                    } else {
                        $floatvalue = floatval($value);
                        if ($floatvalue == 0) {
                            $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                            $errors = true;
                        }
                    }
                }
            }
            // Проверка полей с типом "текст" на наличие запрещенных тегов HTML
            if ($work_base->type_is_text()) {
                // поиск в таблице items значение с таким же названием и base_id
                $name_lang_value = null;
                $name_lang_key = null;
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if ($i == 0) {
                        $name_lang_key = $key;
                        $name_lang_value = $value;
                    }
                    if ($link->parent_base->is_one_value_lst_str_txt == false) {
                        // начиная со второго(индекс==1) элемента массива языков учитывать
                        if ($i > 0) {
                            $name_lang_key = $key . '_' . $lang_key;
                            $name_lang_value = $strings_inputs[$name_lang_key];
                        }
                    }
                    $text_html_check = GlobalController::text_html_check($name_lang_value);
                    if ($text_html_check['result'] == true) {
                        $array_mess[$name_lang_key] = $text_html_check['message'] . '!';
                        $errors = true;
                    }
                    $i = $i + 1;
                }
            }
        }

        if ($errors) {
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

// Одно значение у всех языков
        if ($base->is_one_value_lst_str_txt == true) {
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }

// при создании записи "$item->created_user_id" заполняется
        $item->created_user_id = Auth::user()->id;
        $item->updated_user_id = Auth::user()->id;

        try {
            // начало транзакции
            DB::transaction(function ($r) use ($item, $it_texts, $keys, $values, $strings_inputs) {
                // При добавлении записи
                // Эта команда "$item->save();" нужна, чтобы при сохранении записи стало известно значение $item->id.
                // оно нужно в функции save_main() (для команды "$main->child_item_id = $item->id;");
                $item->save();

                // Присвоение $item->id при $link->parent_is_base_link и при добавлении записи
                foreach ($keys as $index => $value) {
                    $link = Link::findOrFail($value);
                    // Проверка Показывать Связь с признаком "Ссылка на основу"
                    if ($link->parent_is_base_link == true) {
                        $values[$index] = $item->id;
                    }
                }

                // тип - текст
                if ($it_texts) {
                    if ($item->base->type_is_text()) {
                        //$text = $item->text();
                        $text = Text::where('item_id', $item->id)->first();
                        if (!$text) {
                            $text = new Text();
                            $text->item_id = $item->id;
                        }
                        $text->name_lang_0 = $it_texts['name_lang_0'];
                        // Одно значение у всех языков для тип - текст
                        if ($item->base->is_one_value_lst_str_txt == true) {
                            $text->name_lang_1 = $text->name_lang_0;
                            $text->name_lang_2 = $text->name_lang_0;
                            $text->name_lang_3 = $text->name_lang_0;
                        } else {
                            $text->name_lang_1 = "";
                            $text->name_lang_2 = "";
                            $text->name_lang_3 = "";
                            foreach ($it_texts as $it_key => $it_text) {
                                $text[$it_key] = $it_texts[$it_key];
                            }
                        }
                        $text->save();
                    }
                }


//              после ввода данных в форме массив состоит:
//              индекс массива = link_id (для занесения в links->id)
//              значение массива = item_id (для занесения в mains->parent_item_id)
                $i_max = count($keys);

//                // Предыдущий вариант
//                $mains = Main::where('child_item_id', $item->id)->get();
//                $i = 0;
//                foreach ($mains as $main) {
//                    if ($i < $i_max) {
//                        $this->save_main($main, $item, $keys, $values, $i, $strings_inputs);
//                        $i = $i + 1;
//                    } else {
//                        $main->delete();
//                    }
//                }
//                for ($i; $i < $i_max; $i++) {
//                    $main = new Main();
//                    $this->save_main($main, $item, $keys, $values, $i, $strings_inputs);
//                }

                // Проверку можно убрать, т.к. $item создается
                // Новый вариант
                // Сначала проверка, потом присвоение
                // Проверка на $main->link_id, если такой не найден - то удаляется
                $mains = Main::where('child_item_id', $item->id)->get();
                foreach ($mains as $main) {
                    $delete_main = false;
                    $link = Link::where('id', $main->link_id)->first();
                    if ($link) {
                        if ($link->child_base_id != $item->base_id) {
                            $delete_main = true;
                        }
                    } else {
                        $delete_main = true;
                    }
                    if ($delete_main) {
                        $main->delete();
                    }
                }

                $valits = $values;
                // Присвоение данных для $this->save_sets()
                // "$i = 0" использовать, т.к. индексы в массивах начинаются с 0
                $i = 0;

                foreach ($keys as $key) {
                    $main = Main::where('child_item_id', $item->id)->where('link_id', $key)->first();
                    if ($main == null) {
                        $main = new Main();
                        // при создании записи "$item->created_user_id" заполняется
                        $main->created_user_id = Auth::user()->id;
                    } else {
                        // удалить файл-предыдущее значение при корректировке
                        if ($main->parent_item->base->type_is_image() || $main->parent_item->base->type_is_document()) {
                            if ($values[$i] != "") {
                                Storage::delete($main->parent_item->filename());
                            }
                        }
                    }
                    $this->save_main($main, $item, $keys, $values, $valits, $i, $strings_inputs);
                    // "$i = $i + 1;" использовать здесь, т.к. индексы в массивах начинаются с 0
                    $i = $i + 1;
                }

                $rs = $this->calc_value_func($item);
                if ($rs != null) {
                    $item->name_lang_0 = $rs['calc_lang_0'];
                    $item->name_lang_1 = $rs['calc_lang_1'];
                    $item->name_lang_2 = $rs['calc_lang_2'];
                    $item->name_lang_3 = $rs['calc_lang_3'];
                }
                // В ext_store() вызывается один раз, т.к. запись создается
                // При reverse = false передаем null
                $this->save_sets($item, $keys, $values, $valits, false);

                $item->save();

            }, 3);  // Повторить три раза, прежде чем признать неудачу
            // окончание транзакции

        } catch (Exception $exc) {
            return trans('transaction_not_completed') . ": " . $exc->getMessage();
        }

        if (env('MAIL_ENABLED') == 'yes') {
            $base_right = GlobalController::base_right($item->base, $role);
            if ($base_right['is_edit_email_base_create'] == true) {
                $email_to = $item->project->user->email;
                $appname = config('app.name', 'Abakus');
                try {
                    Mail::send(['html' => 'mail/item_create'], ['item' => $item],
                        function ($message) use ($email_to, $appname, $item) {
                            $message->to($email_to, '')->subject(trans('main.new_record') . ' - ' . $item->base->name());
                            $message->from(env('MAIL_FROM_ADDRESS', ''), $appname);
                        });
                } catch (Exception $exc) {
                    return trans('error_sending_email') . ": " . $exc->getMessage();
                }
            }
        }

//return $heading ? redirect()->route('item.item_index', $item) : redirect(session('links'));
        return $heading ? redirect()->route('item.item_index', $item) : redirect()->route('item.base_index', ['base' => $base, 'project' => $project, 'role' => $role]);
//return redirect()->route('item.base_index', ['base'=>$item->base, 'project'=>$item->project, 'role'=>$role]);

    }
// save_info_sets() выполняет все присваивания для $item с отниманием/прибавлением значений
// $reverse = true - отнимать, false - прибавлять
//private
    function save_info_sets(Item $item, bool $reverse)
    {
        $itpv = Item::findOrFail($item->id);
        $mains = $itpv->child_mains()->get();
        $inputs_reverse = array();
        foreach ($mains as $key => $main) {
            $inputs_reverse[$main->link_id] = $main->parent_item_id;
        }
//
//        $valits_previous = null;
//        if ($reverse == true) {
//            $item_previous = Item::where('base_id', $itpv->base_id)->where('base_id', $itpv->base_id)->first();
//            $mains = $itpv->child_mains()->get();
//            $inputs_previous = array();
//            foreach ($mains as $key => $main) {
//                $inputs_previous[$main->link_id] = $main->parent_item_id;
//            }
//            $valits_previous = array_values($inputs_previous);
//        }

        $invals = array();
        foreach ($inputs_reverse as $key => $value) {
            $item_work = Item::findOrFail($value);
            $var = $item_work->numval();
            if ($var['result'] == true) {
                $invals[$key] = $var['value'];
            } else {
                $invals[$key] = $inputs_reverse[$key];
            }
        }

        $keys_reverse = array_keys($inputs_reverse);
        $values_reverse = array_values($invals);
        $valits_reverse = array_values($inputs_reverse);
        $this->save_sets($itpv, $keys_reverse, $values_reverse, $valits_reverse, $reverse);
    }

// Проверка на возможность выполнения присваиваний для переданного $item
    private
    function is_save_sets(Item $item)
    {
        $set_main = Set::select(DB::Raw('sets.*, lt.child_base_id as to_child_base_id, lt.parent_base_id as to_parent_base_id'))
            ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
            ->where('lf.child_base_id', '=', $item->base_id)
            ->where('sets.is_savesets_enabled', '=', true)
            ->orderBy('sets.serial_number')
            ->orderBy('sets.link_from_id')
            ->orderBy('sets.link_to_id')->get();
        $result = null;
        if ($set_main) {
            if (count($set_main) > 0) {
                $result = true;
            }
        }

        return $result;

    }

//    Эти функции похожи:
//save_sets()
//get_item_from_parent_output_calculated_table()
//get_sets_group()
//get_parent_item_from_output_calculated_table()
// Обрабатывает присваивания
// $valits_previous - предыщения значения $valits при $reverse = true и обновлении данных = замена
    private
    function save_sets(Item $item, $keys, $values, $valits, bool $reverse)
    {
//        $table1 = Set::select(DB::Raw('sets.*'))
//            ->join('links', 'sets.link_from_id', '=', 'links.id')
//            ->join('bases', 'links.child_base_id', '=', $item->base_id)
//            ->orderBy('sets.link_from_id')
//            ->orderBy('sets.link_to_id')->get();
        $kf = $reverse == true ? -1 : 1;
        $set_main = Set::select(DB::Raw('sets.*, lt.child_base_id as to_child_base_id, lt.parent_base_id as to_parent_base_id'))
            ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
            ->where('lf.child_base_id', '=', $item->base_id)
            ->where('sets.is_savesets_enabled', '=', true)
            ->orderBy('sets.serial_number')
            ->orderBy('sets.link_from_id')
            ->orderBy('sets.link_to_id')->get();

        // Группировка $set_main по serial_number, индексы массива - serial_number
        $set_group_by_serial_number = $set_main->groupBy('serial_number')->
        sortBy('serial_number');

        //$table2 = Set::select(DB::Raw('$table1.*'))->get();
        // Цикл по записям, в каждой итерации цикла свой порядковый номер $sn_key
        foreach ($set_group_by_serial_number as $sn_key => $sn_value) {
            // Группировка $set_main по to_child_base_id, индексы массива - to_child_base_id
            //             + нужный фильтр "where('serial_number', '=', $sn_key)"
            $set_group_by_base_to = $set_main->where('serial_number', '=', $sn_key)->
            groupBy('to_child_base_id')->
            sortBy('to_child_base_id');
            // Цикл по записям, в каждой итерации цикла свой to_child_base_id в переменной $to_key
            foreach ($set_group_by_base_to as $to_key => $to_value) {
                // Выборка из $set_main
                // "where('serial_number', '=', $sn_key)" нужно
                $set_base_to = $set_main->where('serial_number', '=', $sn_key)->
                where('to_child_base_id', '=', $to_key)->
                sortBy('to_parent_base_id');

                // Группировка данных
                $set_is_group = $set_base_to->where('is_group', true);

                $items = Item::where('base_id', $to_key)->where('project_id', $item->project_id);

                $error = true;
                $found = false;
                $item_seek = null;

                // Поиск $item_seek в цикле
                foreach ($set_is_group as $key => $value) {
//                проверка, если link - вычисляемое поле
                    //if ($link->parent_is_parent_related == true || $link->parent_is_numcalc == true)
                    if ($value->link_from->parent_is_parent_related == true) {

                    } else {
                        //$item_seek = MainController::view_info($item, $value['link_from_id']);
                        $nk = -1;
                        foreach ($keys as $k => $v) {
                            if ($v == $value['link_from_id']) {
                                $nk = $k;
                                break;
                            }
                        }

                        if ($nk != -1) {
                            $set_to = $set_is_group->where('link_from_id', $value['link_from_id'])->first();
                            if ($set_to) {
                                $nt = $set_to->link_to_id;
                                $nv = $values[$nk];
                                $items = $items->whereHas('child_mains', function ($query) use ($nt, $nv) {
                                    $query->where('link_id', $nt)->where('parent_item_id', $nv);
                                });
                                // похожие строки чуть ниже
                                $item_seek = $items->first();
                                $error = false;
                                if (!$item_seek) {
                                    $found = false;
                                    break;
                                } else {
                                    $found = true;
                                }
                            }
                        }
                    }
                }
                // Если нет группировки
                if (count($set_is_group) == 0) {
                    // похожие строки чуть выше
                    $item_seek = $items->first();
                    $error = false;
                    if (!$item_seek) {
                        $found = false;
                    } else {
                        $found = true;
                    }
                }
                if (!$error) {

                    $create_item_seek = false;

                    if (!$found) {

                        $create_item_seek = true;

                        // Эта проверка сделана, чтобы зря не создавать $item_seek
                        // Фильтры 111 - похожие строки ниже
                        foreach ($set_base_to as $key => $value) {
                            $nk = -1;
                            foreach ($keys as $k => $v) {
                                if ($v == $value['link_from_id']) {
                                    $nk = $k;
                                    break;
                                }
                            }
                            if ($nk == -1) {
                                $create_item_seek = false;
                                break;
                            }
                        }

                        if ($create_item_seek == true) {
                            // создать новую запись
                            $item_seek = new Item();
                            $item_seek->base_id = $to_key;
                            $item_seek->project_id = $item->project_id;
                            $item_seek->code = uniqid($item_seek->id . '_', true);
                            $item_seek->name_lang_0 = "";
                            $item_seek->name_lang_1 = "";
                            $item_seek->name_lang_2 = "";
                            $item_seek->name_lang_3 = "";
                            $item_seek->created_user_id = Auth::user()->id;
                            $item_seek->updated_user_id = Auth::user()->id;
                            // Нужно, чтобы id было
                            $item_seek->save();
                        }
                    } else {
                        // "$create_item_seek = true;" нужно
                        $create_item_seek = true;
                        // true - с реверсом
                        $this->save_info_sets($item_seek, true);

                    }
                    if ($create_item_seek == true) {
                        //$items = $items->get();
                        $error = true;
                        $found = false;
                        $valnull = false;

                        // Фильтры 111 - похожие строки выше
                        foreach ($set_base_to as $key => $value) {
                            $nk = -1;
                            foreach ($keys as $k => $v) {
                                if ($v == $value['link_from_id']) {
                                    $nk = $k;
                                    break;
                                }
                            }
                            if ($nk != -1) {
                                $nt = $value->link_to_id;
                                $nv = $values[$nk];
                                $main = Main::where('link_id', $nt)->where('child_item_id', $item_seek->id)->first();
                                $error = false;
                                $vl = 0;
                                if (!$main) {
                                    $main = new Main();
                                    // при создании записи "$item->created_user_id" заполняется
                                    $main->created_user_id = Auth::user()->id;

                                    $main->link_id = $nt;
                                    $main->child_item_id = $item_seek->id;
                                    $vl = 0;
                                } else {
                                    $vl = $main->parent_item->numval()['value'];
                                }
                                $main->updated_user_id = Auth::user()->id;

                                // "$seek_item = false" нужно
                                // "$seek_value = 0" нужно
                                $seek_item = false;
                                $seek_value = 0;
                                $delete_main = false;

                                if ($value->link_to->parent_base->type_is_number() && is_numeric($values[$nk])) {
                                    $ch = $values[$nk];
                                } else {
                                    $ch = 0;
                                }

                                if ($value->is_group == true) {
                                    $main->parent_item_id = $valits[$nk];
                                } elseif ($value->is_update == true) {
                                    if ($value->is_upd_plussum == true || $value->is_upd_pluscount == true) {
                                        // Учет Количества
                                        if ($value->is_upd_pluscount == true) {
                                            $ch = 1;
                                        }
                                        $seek_item = true;
                                        $seek_value = $vl + $kf * $ch;
                                        // Удалить запись с нулевым значением при обновлении
                                        if ($value->is_upd_delete_record_with_zero_value == true) {
                                            if ($seek_value == 0) {
                                                $valnull = true;
                                            }
                                        }
                                    } elseif ($value->is_upd_minussum == true || $value->is_upd_minuscount == true) {
                                        // Учет Количества
                                        if ($value->is_upd_minuscount == true) {
                                            $ch = 1;
                                        }
                                        $seek_item = true;
                                        $seek_value = $vl - $kf * $ch;
                                        // Удалить запись с нулевым значением при обновлении
                                        if ($value->is_upd_delete_record_with_zero_value == true) {
                                            if ($seek_value == 0) {
                                                $valnull = true;
                                            }
                                        }
                                    } elseif ($value->is_upd_replace == true) {
                                        if ($reverse == false) {
                                            $main->parent_item_id = $valits[$nk];
                                            // Удалить запись с нулевым значением при обновлении
                                            if ($value->is_upd_delete_record_with_zero_value == true) {
                                                $item_numval = Item::findOrFail($main->parent_item_id);
                                                $numval = $item_numval->numval();
                                                if ($numval["result"] == true) {
                                                    if ($numval["value"] == 0) {
                                                        $valnull = true;
                                                    }
                                                }
                                            }
                                        } else {
                                            $delete_main = true;
                                            // Используем $valits_previous[$nk]
//                                            $main->parent_item_id = $valits_previous[$nk];
                                            // Удалить запись с нулевым значением при обновлении
                                            if ($value->is_upd_delete_record_with_zero_value == true) {
                                                $valnull = true;
                                            }
                                        }
                                    }
                                    // При $reverse == false
                                    // и при корректировке записи(если подкорректировано поле группировки)
                                    // и при удалении записи
                                    // работает некорректно
                                    // При $reverse == true работает корректно
//                                    elseif ($value->is_upd_cl_gr_first == true || $value->is_upd_cl_gr_last == true) {
//                                        $calc = "";
//                                        if ($value->is_upd_cl_gr_first == true) {
//                                            $calc = "first";
//                                        } elseif ($value->is_upd_cl_gr_last == true) {
//                                            $calc = "last";
//                                        }
//                                        // Расчет Первый(), Последний()
//                                        $item_calc = self::get_item_from_parent_output_calculated_firstlast_table($item, $value, $calc);
//                                        if ($item_calc) {
//                                            $main->parent_item_id = $item_calc->id;
//                                        } else {
//                                            $delete_main = true;
//                                        }
//                                    }
                                }
                                if ($delete_main == true) {
                                    $main->delete();
                                } else {
                                    //  Добавление числа в базу данных
                                    if ($seek_item == true) {
                                        $item_find = self::find_save_number($value->link_to->parent_base_id, $item->project_id, $seek_value);
                                        $main->parent_item_id = $item_find->id;
                                    }
                                    $main->save();
                                }
                            }
                        }

                        $rs = $this->calc_value_func($item_seek);
                        if ($rs != null) {
                            $item_seek->name_lang_0 = $rs['calc_lang_0'];
                            $item_seek->name_lang_1 = $rs['calc_lang_1'];
                            $item_seek->name_lang_2 = $rs['calc_lang_2'];
                            $item_seek->name_lang_3 = $rs['calc_lang_3'];
                        }

                        $item_seek->save();

                        // false - без реверса
                        // "$this->save_info_sets()" выполнять перед проверкой на удаление
                        $this->save_info_sets($item_seek, false);

                        // Если "Удалить запись с нулевым значением при обновлении" == true и значение равно нулю,
                        // то удалить запись
                        if ($valnull) {
                            $item_seek->delete();
                        }
                    }
                }
            }
        }
    }

    static function find_save_number($base_id, $project_id, $seek_value)
    {
        $item_find = null;
        $base = Base::find($base_id);
        if ($base) {
            $item_find = Item::where('base_id', $base_id)->where('project_id', $project_id)
                ->where('name_lang_0', GlobalController::save_number_to_item($base, $seek_value))
                ->first();
            // если не найдено
            if (!$item_find) {
                // создание новой записи в items
                $item_find = new Item();
                $item_find->base_id = $base_id;
                // Похожие строки вверху
                $item_find->code = uniqid($item_find->base_id . '_', true);
                // присваивание полям наименование строкового значение числа
                foreach (config('app.locales') as $key => $value) {
                    if ($item_find->base->type_is_number()) {
                        $item_find['name_lang_' . $key] = GlobalController::save_number_to_item($item_find->base, $seek_value);
                    } else {
                        $item_find['name_lang_' . $key] = $seek_value;
                    }
                }
                $item_find->project_id = $project_id;
                // при создании записи "$item->created_user_id" заполняется
                $item_find->created_user_id = Auth::user()->id;
                $item_find->updated_user_id = Auth::user()->id;
                $item_find->save();

            }
        }
        return $item_find;
    }

//    // Вызывается из save_sets()
//    // Вычисление first(), last()
//    static function get_item_from_parent_output_calculated_firstlast_table(Item $item, Set $set, $calc)
//    {
//        $result_item = null;
//        //$set = Set::find($link->parent_output_calculated_table_set_id);
//        if ($set) {
//            // base_id вычисляемой таблицы
//            $calc_table_base_id = $set->link_to->child_base_id;
//            // Не нужно 'where('sets.is_savesets_enabled', '=', false)'
//            $sets_group = Set::select(DB::Raw('sets.*'))
//                ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
//                ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
//                ->where('is_group', true)
//                ->where('lf.child_base_id', '=', $item->base_id)
//                ->where('serial_number', '=', $set->serial_number)
//                ->orderBy('sets.serial_number')
//                ->orderBy('sets.link_from_id')
//                ->orderBy('sets.link_to_id')
//                ->get();
//
//            $items = Item::where('base_id', $item->base_id)->where('project_id', $item->project_id);
//
//            // Цикл по записям, в каждой итерации цикла свой to_child_base_id в переменной $to_key
//            foreach ($sets_group as $to_key => $to_value) {
//                $item_seek = MainController::get_parent_item_from_main($item->id, $to_value->link_from_id);
//                //dd($item_seek);
//                if ($item_seek) {
//                    $items = $items->whereHas('child_mains', function ($query) use ($to_value, $item_seek) {
//                        $query->where('link_id', $to_value->link_from_id)->where('parent_item_id', $item_seek->id);
//                    });
//                }
//            }
//            $item_calc = null;
//            if ($calc == "first") {
//                $item_calc = $items->first();
//            } elseif ($calc == "last") {
//                $item_calc = $items->last();
//            }
//            if ($item_calc) {
//                $result_item = MainController::get_parent_item_from_main($item_calc->id, $set->link_from_id);
//            }
//
//        }
//        return $result_item;
//    }


// "->where('bs.type_is_list', '=', true)" нужно, т.к. запрос функции идет с ext_edit.php
    static function get_sets_group(Base $base, Link $link)
    {
        // "->where('bs.type_is_list', '=', true)" нужно, т.к. запрос функции идет с ext_edit.php
        //->where('sets.is_savesets_enabled', '=', true)
        $set = Set::find($link->parent_output_calculated_table_set_id);
        $sets_group = Set::select(DB::Raw('sets.*'))
            ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
            ->join('bases as bs', 'lf.parent_base_id', '=', 'bs.id')
            ->where('lf.child_base_id', '=', $base->id)
            ->where('is_group', true)
            ->where('bs.type_is_list', '=', true)
            ->where('sets.serial_number', '=', $set->serial_number)
            ->orderBy('sets.serial_number')
            ->orderBy('sets.link_from_id')
            ->orderBy('sets.link_to_id')->get();

        return $sets_group;
    }

    static function get_sets_calcsort(Base $base, Link $link)
    {
        $set = Set::find($link->parent_output_calculated_table_set_id);

        // Не нужно 'where('sets.is_savesets_enabled', '=', false)'
        // Сортировка такая одинаковая:
        // ItemController::get_item_from_parent_output_calculated_table()
        // и SetController::index(),
        // влияет на обработку сортировки
        $sets_calcsort = Set::select(DB::Raw('sets.*'))
            ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
            ->where('is_calcsort', true)
            ->where('lf.child_base_id', '=', $base->id)
            ->where('serial_number', '=', $set->serial_number)
            ->orderBy('sets.serial_number')
            ->orderBy('sets.line_number')
            ->orderBy('lf.child_base_id')
            ->orderBy('lt.child_base_id')
            ->orderBy('lf.parent_base_number')
            ->orderBy('lt.parent_base_number')
            ->get();

        return $sets_calcsort;
    }

// Функции get_item_from_parent_output_calculated_table() и get_parent_item_from_output_calculated_table() похожи,
// выполняют одинаковую функцию: Выводят/считают поле из таблицы
// Первая вызывается из из MainController.php - view_info(), вторая из ext_edit.php,
// Первая возвращает $item, вторая $item->name()

// Вызывается из MainController.php - view_info()
// Выводит поле вычисляемой таблицы
    static function get_item_from_parent_output_calculated_table(Item $item_main, Link $link)
    {
        $result_item = null;
        $set = Set::find($link->parent_output_calculated_table_set_id);
        if ($set) {
            // base_id вычисляемой таблицы
            $calc_table_base_id = $set->link_to->child_base_id;

            $items = Item::where('base_id', $calc_table_base_id)->where('project_id', $item_main->project_id);

            $sets_group = self::get_sets_group($item_main->base, $link);

            // Фильтрация/поиск
            // Цикл по записям, в каждой итерации цикла свой to_child_base_id в переменной $to_key
            foreach ($sets_group as $to_key => $to_value) {
                $item_seek = MainController::get_parent_item_from_main($item_main->id, $to_value->link_from_id);
                if ($item_seek) {
                    $items = $items->whereHas('child_mains', function ($query) use ($to_value, $item_seek) {
                        $query->where('link_id', $to_value->link_to_id)->where('parent_item_id', $item_seek->id);
                    });
                }
            }

            $result_item = self::output_calculated_table_dop($item_main->base, $link, $set, $item_main->project, $items);

        }

        return $result_item;
    }

// Вызывается из ext_edit.php
    static function get_parent_item_from_output_calculated_table(Request $request)
    {
        $params = $request->query();
        $result = trans('main.no_information') . '!';
        $base = null;
        if (array_key_exists('base_id', $params)) {
            $base = Base::find($params['base_id']);
        }
        $link = null;
        if (array_key_exists('link_id', $params)) {
            $link = Link::find($params['link_id']);
        }
        $items_id_group = null;
        if (array_key_exists('items_id_group', $params)) {
            if (is_array($params['items_id_group'])) {
                $items_id_group = $params['items_id_group'];
            }
        }
        //  '&& $items_id_group' не нужно, т.к. группировки может не быть
        if ($base && $link) {
            $result_item = null;
            $set = Set::find($link->parent_output_calculated_table_set_id);
            $sets_group = self::get_sets_group($base, $link);

            // base_id вычисляемой таблицы
            $calc_table_base_id = $set->link_to->child_base_id;

            $item_seek0 = null;
            if (isset($items_id_group[0])) {
                $item_seek0 = Item::find($items_id_group[0]);
            }
            if ($item_seek0) {
                $items = Item::where('base_id', $calc_table_base_id)->where('project_id', $item_seek0->project_id);

                $i = 0;
                // Фильтрация/поиск
                // Цикл по записям, в каждой итерации цикла свой to_child_base_id в переменной $to_key
                foreach ($sets_group as $to_key => $to_value) {
                    $item_seek = null;
                    if (isset($items_id_group[$i])) {
                        $item_seek = Item::find($items_id_group[$i]);
                    }
                    if ($item_seek == null) {
                        break;
                    }
                    $items = $items->whereHas('child_mains', function ($query) use ($to_value, $item_seek) {
                        $query->where('link_id', $to_value->link_to_id)->where('parent_item_id', $item_seek->id);
                    });
                    $i = $i + 1;

                }

                $result_item = self::output_calculated_table_dop($base, $link, $set, $item_seek0->project, $items);

            }
            if ($result_item) {
                $result = $result_item->name(false, true, true);
            }
        }
        return $result;
    }

// Вызывается из get_item_from_parent_output_calculated_table() и get_parent_item_from_output_calculated_table()
    static function output_calculated_table_dop(Base $base, Link $link, Set $set, Project $project, $items)
    {
        $result_item = null;
        $sets_calcsort = self::get_sets_calcsort($base, $link);

        // Обработка сортировки
        // Эти проверки нужны
        // 'link_from_id' не используется при обработке сортировки
        // 'link_to_id' используется при обработке сортировки
        if (($set->is_upd_cl_gr_first == true || $set->is_upd_cl_gr_last == true)
            && ($sets_calcsort) && ($items->count() > 0)) {
            $name = "";  // нужно, не удалять
            $index = array_search(App::getLocale(), config('app.locales'));
            if ($index !== false) {   // '!==' использовать, '!=' не использовать
                $name = 'name_lang_' . $index;
            }
            $collection = collect();
            $items_calcsort = $items->orderBy($name)->get();
            $str = "";
            foreach ($items_calcsort as $item) {
                $str = "";
                foreach ($sets_calcsort as $set_value) {
                    $item_find = MainController::view_info($item->id, $set_value->link_to_id);
                    if ($item_find) {
                        // Формирование вычисляемой строки для сортировки
                        // Для строковых данных для сортировки берутся первые 50 символов
                        if ($item_find->base->type_is_list() || $item_find->base->type_is_string()) {
                            $str = $str . str_pad(trim($item_find[$name]), 50);
                        } else {
                            $str = $str . trim($item_find[$name]);
                        }

                    }
                }
                // В $collection сохраняется в key - $item->id
                $collection[$item->id] = $str;
            }

            //            Сортировка коллекции по значению
            $collection = $collection->sort();
            $ids = $collection->keys()->toArray();

            $items = Item::whereIn('id', $ids)
                ->orderBy(\DB::raw("FIELD(id, " . implode(',', $ids) . ")"));
        }

        $item_calc = null;
        // '$is_func = false;' нужно
        $is_func = false;
        $count = 0;
        $sum = 0;
        // Первый(), Последний()
        if ($set->is_upd_cl_gr_first == true || $set->is_upd_cl_gr_last == true) {
            if ($set->is_upd_cl_gr_first == true) {
                $item_calc = $items->first();
            } elseif ($set->is_upd_cl_gr_last == true) {
                // Нужно '->get()'
                $item_calc = $items->get()->last();
            }

            // Расчет Средний(), Количество(), Сумма()
        } elseif ($set->is_upd_cl_fn_count == true || $set->is_upd_cl_fn_avg == true || $set->is_upd_cl_fn_sum == true) {
            $is_func = true;
            $items_list = $items->get();
            // "$seek_value = 0" нужно
            $seek_value = 0;

            // Расчет Количество()
            if ($set->is_upd_cl_fn_count == true) {
                foreach ($items_list as $item) {
                    $str = "";
                    // Находим в исходной таблице объект, по которуму считается Количество()
                    $item_find = MainController::view_info($item->id, $set->link_to_id);
                    if ($item_find) {
                        $seek_value = $seek_value + 1;
                    }
                }
                $seek_item = $seek_value > 0;

                // Расчет Средний(), Сумма()
            } elseif ($set->is_upd_cl_fn_avg == true || $set->is_upd_cl_fn_sum == true) {
                $count = 0;
                $sum = 0;
                foreach ($items_list as $item) {
                    $str = "";
                    // Находим в исходной таблице объект, по которуму считается Средний(), Сумма()
                    $item_find = MainController::view_info($item->id, $set->link_to_id);
                    if ($item_find) {
                        $count = $count + 1;
                        $sum = $sum + $item_find->numval()['value'];
                    }
                }
                // Расчет Средний()
                if ($set->is_upd_cl_fn_avg == true) {
                    // Если деление на ноль
                    if ($count == 0) {
                        $seek_value = 0;
                    } else {
                        $seek_value = $sum / $count;
                    }
                    // Расчет Сумма()
                } elseif ($set->is_upd_cl_fn_sum == true) {
                    $seek_value = $sum;
                }
                $seek_item = $count > 0;
            }

            // Если есть данные для расчета
            if ($seek_item) {
                $item_calc = self::find_save_number($set->link_from->parent_base_id, $project->id, $seek_value);
            }

        } else {
            $count = $items->count();
            if ($count == 1) {
                $item_calc = $items->first();
            }
        }
        if ($item_calc) {
            // Если данные ($item_calc) уже найдены и посчитаны
            if ($is_func) {
                $result_item = $item_calc;
            } else {
                $result_item = MainController::get_parent_item_from_main($item_calc->id, $set->link_to_id);
            }
        }
        return $result_item;
    }

    private
    function save_main(Main $main, $item, $keys, $values, &$valits, $index, $strings_inputs)
    {
        $main->link_id = $keys[$index];
        $main->child_item_id = $item->id;

        // поиск должен быть удачным, иначе "$main->link_id = $keys[$index]" может дать ошибку
        $link = Link::findOrFail($keys[$index]);

        // тип корректировки поля - список
        if ($link->parent_base->type_is_list()) {
            if ($values[$index] == 0) {
                // Нужно
                // Если запись main существует - то удалить ее
                if (isset($main->id)) {
                    $main->delete();
                }
                // Нужно
                return;
            }
            $main->parent_item_id = $values[$index];

        } // тип корректировки поля - изображение или документ
        elseif ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
            $item_find = Item::find($main->parent_item_id);
            if (!$item_find) {
                // создание новой записи в items
                $item_find = new Item();
            }
            $item_find->base_id = $link->parent_base_id;
            // Похожая строка вверху и внизу
            $item_find->code = uniqid($item_find->base_id . '_', true);
            //присваивание полям наименование строкового значение числа
//            $i = 0;
//            foreach (config('app.locales') as $lang_key => $lang_value) {
//                if ($i == 0) {
//                    $item_find['name_lang_' . $lang_key] = $values[$index];
//                } else {
//                    if ($link->parent_base->is_one_value_lst_str_txt == true) {
//                        // Одно значение для наименований у всех языков
//                        $item_find['name_lang_' . $lang_key] = $values[$index];
//                    } else {
//                        $item_find['name_lang_' . $lang_key] = $strings_inputs[$link->id . '_' . $lang_key];
//                    }
//                }
//                $i = $i + 1;
//            }
            $item_find->name_lang_0 = $values[$index];
            $item_find->name_lang_1 = "";
            if ($item_find->base->type_is_image() == true) {
                if ($item_find->base->is_to_moderate_image == true) {
                    // На модерации
                    $item_find->name_lang_1 = "3";
                    // Похожие строки ниже
                    if (env('MAIL_ENABLED') == 'yes') {
                        $appname = config('app.name', 'Abakus');
                        try {
                            Mail::send(['html' => 'mail/login_site'], ['remote_addr' => $_SERVER['REMOTE_ADDR'],
                                'http_user_agent' => $_SERVER['HTTP_USER_AGENT'], 'appname' => $appname],
                                function ($message) use ($appname) {
                                    $message->to(env('MAIL_TO_ADDRESS_MODERATION', 'moderation@rsb0807.kz'), '')->subject("Модерация '" . $appname . "'");
                                    $message->from(env('MAIL_FROM_ADDRESS', 'support@rsb0807.kz'), $appname);
                                });
                        } catch (Exception $exc) {
                            return trans('error_sending_email') . ": " . $exc->getMessage();
                        }
                    }
                } else {
                    // Без модерации
                    $item_find->name_lang_1 = "0";
                }
            }
            $item_find->name_lang_2 = "";
            $item_find->name_lang_3 = "";

            $item_find->project_id = $item->project_id;
            // при создании записи "$item->created_user_id" заполняется
            $item_find->created_user_id = Auth::user()->id;
            $item_find->updated_user_id = Auth::user()->id;
            $item_find->save();
            $main->parent_item_id = $item_find->id;
            // заменяем значение в массиве ссылкой на $item вместо значения
            $valits[$index] = $item_find->id;

        } // тип корректировки поля - строка
        elseif ($link->parent_base->type_is_string()) {
            if ($link->parent_base->is_required_lst_num_str_txt_img_doc == false) {
                $main_delete = $values[$index] == "";
                if ($link->parent_base->is_one_value_lst_str_txt == false) {
                    $i = 0;
                    foreach (config('app.locales') as $lang_key => $lang_value) {
                        // начиная со второго(индекс==1) элемента массива языков учитывать
                        if ($i > 0) {
                            $main_delete = $main_delete && ($strings_inputs[$link->id . '_' . $lang_key] == "");
                        }
                        $i = $i + 1;
                    }
                }
                if ($main_delete) {
                    // Нужно
                    // Если запись main существует - то удалить ее
                    if (isset($main->id)) {
                        $main->delete();
                    }
                    // Нужно
                    return;
                }
            }
            // поиск в таблице items значение с таким же названием и base_id
            $item_find = Item::where('base_id', $link->parent_base_id)->where('project_id', $item->project_id)->where('name_lang_0', $values[$index]);
            if ($link->parent_base->is_one_value_lst_str_txt == false) {
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    // начиная со второго(индекс==1) элемента массива языков учитывать
                    if ($i > 0) {
                        $item_find = $item_find->where('name_lang_' . $lang_key, $strings_inputs[$link->id . '_' . $lang_key]);
                    }
                    $i = $i + 1;
                }
            }

            $item_find = $item_find->first();

            // если не найдено
            if (!$item_find) {
                // создание новой записи в items
                $item_find = new Item();
                $item_find->base_id = $link->parent_base_id;
                // Похожая строка вверху и внизу
                $item_find->code = uniqid($item_find->base_id . '_', true);
                // присваивание полям наименование строкового значение числа
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if ($i == 0) {
                        $item_find['name_lang_' . $lang_key] = $values[$index];
                    } else {
                        if ($link->parent_base->is_one_value_lst_str_txt == true) {
                            // Одно значение для наименований у всех языков
                            $item_find['name_lang_' . $lang_key] = $values[$index];
                        } else {
                            $item_find['name_lang_' . $lang_key] = $strings_inputs[$link->id . '_' . $lang_key];
                        }
                    }
                    $i = $i + 1;
                }
                $item_find->project_id = $item->project_id;
                // при создании записи "$item->created_user_id" заполняется
                $item_find->created_user_id = Auth::user()->id;
                $item_find->updated_user_id = Auth::user()->id;
                $item_find->save();
            }
            $main->parent_item_id = $item_find->id;
            // заменяем значение в массиве ссылкой на $item вместо значения
            $valits[$index] = $item_find->id;


        } // тип корректировки поля - текст
        // Полные значения полей text хранятся в таблице texts,
        // краткие (ограниченные 255 - размером полей хранятся в $item->name_lang_0 - $item->name_lang_3)
        // связь между таблицами items и text - "один-к-одному", по полю $item->id = $text->item->id
        elseif ($link->parent_base->type_is_text()) {
            if ($link->parent_base->is_required_lst_num_str_txt_img_doc == false) {
                $main_delete = $values[$index] == "";
                if ($link->parent_base->is_one_value_lst_str_txt == false) {
                    $i = 0;
                    foreach (config('app.locales') as $lang_key => $lang_value) {
                        // начиная со второго(индекс==1) элемента массива языков учитывать
                        if ($i > 0) {
                            $main_delete = $main_delete && ($strings_inputs[$link->id . '_' . $lang_key] == "");
                        }
                        $i = $i + 1;
                    }
                }
                if ($main_delete) {
                    // Нужно
                    // Если запись main существует - то удалить ее
                    if (isset($main->id)) {
                        $main->delete();
                    }
                    // Нужно
                    return;
                }
            }
            $item_find = Text::find($main->parent_item_id);
            if (!$item_find) {
                // создание новой записи в items
                $item_find = new Item();
                // при создании записи "$item->created_user_id" заполняется
                $item_find->created_user_id = Auth::user()->id;
            }
            $item_find->base_id = $link->parent_base_id;
            // Похожая строка вверху и внизу
            $item_find->code = uniqid($item_find->base_id . '_', true);
            $item_find->project_id = $item->project_id;
            $item_find->updated_user_id = Auth::user()->id;

            // Нужно чтобы знать $item_find->id в команде "$text->item_id = $item_find->id;"
            $item_find->save();

            //$text = $item->text();
            $text = Text::where('item_id', $item_find->id)->first();
            if (!$text) {
                $text = new Text();
                $text->item_id = $item_find->id;
            }

            // присваивание полям наименование строкового значение числа
            $i = 0;
            foreach (config('app.locales') as $lang_key => $lang_value) {
                if ($i == 0) {
                    $item_find['name_lang_' . $lang_key] = GlobalController::itnm_left($values[$index]);
                    $text['name_lang_' . $lang_key] = $values[$index];
                } else {
                    if ($link->parent_base->is_one_value_lst_str_txt == true) {
                        // Одно значение для наименований у всех языков
                        $item_find['name_lang_' . $lang_key] = GlobalController::itnm_left($values[$index]);
                        $text['name_lang_' . $lang_key] = $values[$index];
                    } else {
                        $item_find['name_lang_' . $lang_key] = GlobalController::itnm_left($strings_inputs[$link->id . '_' . $lang_key]);
                        $text['name_lang_' . $lang_key] = $strings_inputs[$link->id . '_' . $lang_key];
                    }
                }
                $i = $i + 1;
            }
            // Нужно чтобы сохранить name_lang_0 - name_lang_3
            $item_find->save();

            $text->save();
            $main->parent_item_id = $item_find->id;
            // заменяем значение в массиве ссылкой на $item вместо значения
            $valits[$index] = $item_find->id;

            // тип корректировки поля - не строка и не список
        } else {

            // Проверка числовых полей
            // Если равно нулю и "$link->parent_base->is_required_lst_num_str_txt_img_doc == false",
            // удаляет запись $main, если она есть
            // и в итоге: вместо нуля отображается null/empty
//            if (($link->parent_base->type_is_number()) && ($link->parent_base->is_required_lst_num_str_txt_img_doc == false)) {
//                if ($values[$index] == 0) {
//                    // Нужно
//                    // Если запись main существует - то удалить ее
//                    if (isset($main->id)) {
//                        $main->delete();
//                    }
//                    // Нужно
//                    return;
//                }
//            }

            // поиск в таблице items значение с таким же названием и base_id
            $item_find = Item::where('base_id', $link->parent_base_id)->where('project_id', $item->project_id)->where('name_lang_0', $values[$index])->first();

            // если не найдено
            if (!$item_find) {
                // создание новой записи в items
                $item_find = new Item();
                $item_find->base_id = $link->parent_base_id;
                // Похожие строки вверху
                $item_find->code = uniqid($item_find->base_id . '_', true);
                // присваивание полям наименование строкового значение числа
                foreach (config('app.locales') as $key => $value) {
                    $item_find['name_lang_' . $key] = $values[$index];
                }
                $item_find->project_id = $item->project_id;
                // при создании записи "$item->created_user_id" заполняется
                $item_find->created_user_id = Auth::user()->id;
                $item_find->updated_user_id = Auth::user()->id;
                $item_find->save();
            }
            $main->parent_item_id = $item_find->id;
            // заменяем значение в массиве ссылкой на $item вместо значения
            $valits[$index] = $item_find->id;
        }
        $main->updated_user_id = Auth::user()->id;
        $main->save();
    }

    function save_img_doc(Request $request, Item &$item)
    {
        $base = $item->base;
        if ($base->type_is_image() || $base->type_is_document()) {
            $path = "";
            if ($request->hasFile('name_lang_0')) {
                $path = $item->name_lang_0->store('public/' . $item->project_id . '/' . $base->id);
                $item->name_lang_0 = $path;
                if ($base->type_is_image()) {
                    if ($item->base->is_to_moderate_image == true) {
                        // На модерации
                        $item->name_lang_1 = "3";

                        // Похожие строки выше
                        if (env('MAIL_ENABLED') == 'yes') {
                            $appname = config('app.name', 'Abakus');
                            try {
                                Mail::send(['html' => 'mail/login_site'], ['remote_addr' => $_SERVER['REMOTE_ADDR'],
                                    'http_user_agent' => $_SERVER['HTTP_USER_AGENT'], 'appname' => $appname],
                                    function ($message) use ($appname) {
                                        $message->to(env('MAIL_TO_ADDRESS_MODERATION', 'moderation@rsb0807.kz'), '')->subject("Модерация '" . $appname . "'");
                                        $message->from(env('MAIL_FROM_ADDRESS', 'support@rsb0807.kz'), $appname);
                                    });
                            } catch (Exception $exc) {
                                return trans('error_sending_email') . ": " . $exc->getMessage();
                            }
                        }
                    } else {
                        // Без модерации
                        $item->name_lang_1 = "0";
                    }
                } else {
                    // В $item->name_lang_1 хранится наименование документа
                    //$item->name_lang_1 = "";
                }
                $item->name_lang_2 = "";
                $item->name_lang_3 = "";
            } else {
                // Проверка существует ли переменная '$request->name_lang_0_img_doc_delete'
//                if (isset($request->name_lang_0_img_doc_delete)) {
//                    // $delete = true, если отметка поставлена, = false без отметки'
//                    $delete = isset($request->name_lang_0_img_doc_delete) ? true : false;
//                    if ($delete == true) {
//                        if ($item->img_doc_exist()) {
//                            // Удаление изображения или документа
//                            Storage::delete($item->filename());
//                            $item->name_lang_0 = "";
//                            // Без модерации
//                            $item->name_lang_1 = "";
//                            $item->name_lang_2 = "";
//                            $item->name_lang_3 = "";
//                            $item->save();
//                        }
//                    }
//                }
            }
        }
    }

    function store(Request $request)
    {
        //$request->validate($this->rules($request));

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $item = new Item($request->except('_token', '_method'));

        $item->base_id = $request->base_id;
        $item->name_lang_0 = $request->name_lang_0;

        $item->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $item->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $item->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $item->save();

//        return redirect()->route('item.base_index', $item->base->id);
        return redirect(session('links'));
    }

    function update(Request $request, Item $item)
    {
        // Если данные изменились - выполнить проверку
//        if (!(($item->base_id == $request->base_id) and ($item->name_lang_0 == $request->name_lang_0))) {
//            $request->validate($this->rules($request));
//        }

        $data = $request->except('_token', '_method');

        $item->fill($data);

        $item->base_id = $request->base_id;
        $item->name_lang_0 = $request->name_lang_0;
        $item->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $item->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $item->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $item->save();

//        return redirect()->route('item.base_index', $item->base->id);
        return redirect(session('links'));
    }

    function ext_update(Request $request, Item $item, Role $role)
    {
        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        if (GlobalController::check_project_user($item->project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        // Если данные изменились - выполнить проверку. оператор '??' нужны
        if (!($item->name_lang_0 ?? '' == $request->name_lang_0 ?? '')) {
            $request->validate($this->name_lang_rules($request));
        }
        if (!($item->code == $request->code)) {
            $request->validate($this->code_rules($request, $item->project_id, $item->base_id));
        }

        // Проверка полей с типом "текст" на длину текста
        if ($item->base->type_is_text() && $item->base->length_txt > 0) {
            $errors = false;
            foreach (config('app.locales') as $lang_key => $lang_value) {
                if (strlen($request['name_lang_' . $lang_key]) > $item->base->length_txt) {
                    $array_mess['name_lang_' . $lang_key] = trans('main.length_txt_rule') . ' ' . $item->base->length_txt . '!';
                    $errors = true;
                }
            }
            if ($errors) {
                // повторный вызов формы
                return redirect()->back()
                    ->withInput()
                    ->withErrors($array_mess);
            }
        }

        // Проверка на обязательность ввода
        if ($item->base->is_required_lst_num_str_txt_img_doc == true && $item->base->is_calcname_lst == false) {
            // Тип - список, строка или текст
            if ($item->base->type_is_list() || $item->base->type_is_string() || $item->base->type_is_text()) {
                $name_lang_array = array();
                // значения null в ""
                $name_lang_array[0] = isset($request->name_lang_0) ? $request->name_lang_0 : "";
                $name_lang_array[1] = isset($request->name_lang_1) ? $request->name_lang_1 : "";
                $name_lang_array[2] = isset($request->name_lang_2) ? $request->name_lang_2 : "";
                $name_lang_array[3] = isset($request->name_lang_3) ? $request->name_lang_3 : "";
                $errors = false;
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if (($item->base->is_one_value_lst_str_txt == true && $lang_key == 0) || ($item->base->is_one_value_lst_str_txt == false)) {
                        if ($name_lang_array[$i] === '') {
                            $array_mess['name_lang_' . $i] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                            $errors = true;
                        }
                        $i = $i + 1;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - число
            } elseif ($item->base->type_is_number()) {
                // значения null в "0"
                $name_lang_0_val = isset($request->name_lang_0) ? $request->name_lang_0 : "0";
                $errors = false;
                // "$value === '0'" использовать для точного сравнения (например, при $link->parent_base->type_is_string())
                if ($name_lang_0_val === '0') {
                    $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                    $errors = true;
                } else {
                    $floatvalue = floatval($name_lang_0_val);
                    if ($floatvalue == 0) {
                        $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                        $errors = true;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - изображение
            } elseif
            ($item->base->type_is_image()) {
                $errors = false;
                if (!$item->img_doc_exist()) {
                    if (!$request->hasFile('name_lang_0')) {
                        $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                        $errors = true;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - документ
            } elseif
            ($item->base->type_is_document()) {
                $errors = false;
                if (!$item->img_doc_exist()) {
                    if (!$request->hasFile('name_lang_0')) {
                        $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                        $errors = true;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
            }
        }

        // Проверка полей с типом "текст" на наличие запрещенных тегов HTML
        if ($item->base->type_is_text()) {
            $errors = false;
            foreach (config('app.locales') as $lang_key => $lang_value) {
                $text_html_check = GlobalController::text_html_check($request['name_lang_' . $lang_key]);
                if ($text_html_check['result'] == true) {
                    $array_mess['name_lang_' . $lang_key] = $text_html_check['message'] . '!';
                    $errors = true;
                }
            }
            if ($errors) {
                // повторный вызов формы
                return redirect()->back()
                    ->withInput()
                    ->withErrors($array_mess);
            }
        }

        if ($item->base->type_is_image() || $item->base->type_is_document()) {
            if ($request->hasFile('name_lang_0')) {
                $fs = $request->file('name_lang_0')->getSize();
                $mx = $item->base->maxfilesize_img_doc;
                if ($fs > $mx) {
                    $errors = false;
                    if ($request->file('name_lang_0')->isValid()) {
                        $array_mess['name_lang_0'] = self::filesize_message($fs, $mx);
                        $errors = true;
                    }
                    if ($errors) {
                        // повторный вызов формы
                        return redirect()->back()
                            ->withInput()
                            ->withErrors($array_mess);
                    }
                }
            }
        }
        if ($item->base->type_is_image() || $item->base->type_is_document()) {
            if ($request->hasFile('name_lang_0')) {
                Storage::delete($item->filename());
            }
        }

        $data = $request->except('_token', '_method');
        $item->fill($data);
        //$item->project_id = $request->project_id;
        $item->updated_user_id = Auth::user()->id;
        //$role = Role::findOrFail($request->role_id);

        // Похожая проверка в ext_edit.blade.php
//        if ($item->base->is_code_needed == true && $item->base->is_code_number == true && $item->base->is_limit_sign_code == true
//            && $item->base->is_code_zeros == true && $item->base->is_code_zeros > 0) {
//            // Дополнить код слева нулями
//            $item->code = str_pad($item->code, $item->base->significance_code, '0', STR_PAD_LEFT);
//        }

        //$item->base_id = $item->base_id;

        // нужно по порядку: сначала этот блок
        // значения null в ""
        // у строк могут быть пустые значения, поэтому нужно так: '$item->name_lang_0 = isset($request->name_lang_0) ? $request->name_lang_0 : ""'
        // Проверка "if (!($item->base->type_is_image() || $item->base->type_is_document()))" нужна
        if (!($item->base->type_is_image() || $item->base->type_is_document())) {
            $item->name_lang_0 = isset($request->name_lang_0) ? $request->name_lang_0 : "";
            $item->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
            $item->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
            $item->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";
        }

        // далее этот блок
        // похожие формула выше (в этой же процедуре)

        // тип - логический
        if ($item->base->type_is_boolean()) {
            $item->name_lang_0 = isset($request->name_lang_0) ? "1" : "0";

        } // тип - число
        elseif ($item->base->type_is_number()) {
            $item->name_lang_0 = GlobalController::save_number_to_item($item->base, $request->name_lang_0);

        } // тип - текст
        elseif ($item->base->type_is_text()) {
            $item->name_lang_0 = GlobalController::itnm_left($request->name_lang_0);
            $item->name_lang_1 = GlobalController::itnm_left($request->name_lang_1);
            $item->name_lang_2 = GlobalController::itnm_left($request->name_lang_2);
            $item->name_lang_3 = GlobalController::itnm_left($request->name_lang_3);
        }

        // затем этот блок (используется "$item->base")
        if ($item->base->type_is_number() || $item->base->type_is_date() || $item->base->type_is_boolean()) {
            // присваивание полям наименование строкового значение числа/даты
//            foreach (config('app.locales') as $key => $value) {
//                if ($key > 0) {
//                    $item['name_lang_' . $key] = $item->name_lang_0;
//                }
//            }
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }

        $this::save_img_doc($request, $item);

        $excepts = array('_token', 'code', '_method', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3');
        $string_langs = $this->get_child_links($item->base);

        // Формируется массив $code_names - названия полей кодов
        // Формируется массив $string_names - названия полей наименование
        $code_names = array();
        $string_names = array();
        $i = 0;
        foreach ($string_langs as $key => $link) {
            if ($link->parent_base->type_is_string() || $link->parent_base->type_is_text()) {
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    // начиная со второго(индекс==1) элемента массива языков сохранять
                    if ($i > 0) {
                        // для первого (нулевого) языка $input_name = $key ($link->id)
                        // для последующих языков $input_name = $key . '_' . $lang_key($link->id . '_' . $lang_key);
                        // это же правило используется в ext_edit.blade.php
                        //$string_names[] = $link->id . ($lang_key == 0) ? '' : '_' . $lang_key;  // так не работает, дает '' в результате
                        $string_names[] = ($lang_key == 0) ? $link->id : $link->id . '_' . $lang_key;  // такой вариант работает
                    }
                    $i = $i + 1;
                }
            }
            if ($link->parent_is_enter_refer == true) {
                $code_names[] = 'code' . $link->id;
            }
        }
        // при корректировке base (например, список основы Изображение) не используется - не нужно: можно выполнить удаление изображения
        // Только при корректировке записи используется массив $del_names()
        // Формируется массив $del_names - названия полей "Удалить изображение"/"Удалить документ"
        // массив $del_links - список links для удаления
        $del_names = array();
        $del_links = array();
        foreach ($string_langs as $key => $link) {
            if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                $i = 0;
                // Проверка:
                // 1) Поле 'link->id' существует в $request
                // 2) Поле 'link->id' будет существовать в $request, если на форме выбран файл (изображение или документ)
                $is_img_doc = isset($request[$link->id]);
                // Две проверки:
                // 1) на наличие вводимого поля
                // 2) в введенном поле поставлена отметка
                $is_del = isset($request[$link->id . '_img_doc_delete']);
                if ($is_del) {
                    $del_names[] = $link->id . '_img_doc_delete';
                    if (!$is_img_doc) {
                        $del_links[] = $link->id;
                    }
                }
            }
        }

        // Только при корректировке записи используется массив $del_names()
        // загрузить в $inputs все поля ввода, кроме $excepts, $string_names, $string_codes, $del_names, array_merge() - функция суммирования двух и более массивов
        $inputs = $request->except(array_merge($excepts, $string_names, $code_names, $del_names));

        $it_texts = null;
        if ($item->base->type_is_text()) {
            $only = array('name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3');
            $it_texts = $request->only($only);

            foreach ($it_texts as $it_key => $it_text) {
                $it_texts[$it_key] = isset($it_texts[$it_key]) ? $it_texts[$it_key] : "";
            }

        }

        // Проверка существования кода объекта
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            if ($link->parent_base->is_code_needed == true && $link->parent_is_enter_refer == true) {
                $item_needed = Item::find($value);
                if (!$item_needed) {
                    $array_mess['code' . $key] = trans('main.code_not_found') . "!";
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
            }
        }

        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                if ($request->hasFile($link->id)) {
                    $fs = $request->file($link->id)->getSize();
                    $mx = $link->parent_base->maxfilesize_img_doc;
                    if ($fs > $mx) {
                        $errors = false;
                        if ($request->file($link->id)->isValid()) {
                            $array_mess[$link->id] = self::filesize_message($fs, $mx);
                            $errors = true;
                        }
                        if ($errors) {
                            // повторный вызов формы
                            return redirect()->back()
                                ->withInput()
                                ->withErrors($array_mess);
                        }
                    }
                }
            }
        }

        // обработка для логических полей
        // если при вводе формы пометка checkbox не установлена, в $request записи про элемент checkbox вообще нет
        // если при вводе формы пометка checkbox установлена, в $request есть запись со значеним "on"
        // см. https://webformyself.com/kak-v-php-poluchit-znachenie-checkbox/
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                $path = "";
                if ($request->hasFile($key)) {
                    $path = $request[$key]->store('public/' . $item->project_id . '/' . $link->parent_base_id);
                }
                $inputs[$key] = $path;
            } elseif ($link->parent_base->type_is_number()) {
                $inputs[$key] = GlobalController::save_number_to_item($link->parent_base, $value);
            }
        }

//        foreach ($string_langs as $link) {
//            // Проверка нужна
//            $base_link_right = GlobalController::base_link_right($link, $role);
//            if ($base_link_right['is_edit_link_enable'] == false) {
//                continue;
//            }
//            // похожая формула выше (в этой же процедуре)
//            if ($link->parent_base->type_is_boolean()) {
//                // у этой команды два предназначения:
//                // 1) заменить "on" на "1" при отмеченном checkbox
//                // 2) создать новый ([$link->id]-й) элемент массива со значением "0" при выключенном checkbox
//                // в базе данных информация хранится как "0" или "1"
//                $inputs[$link->id] = isset($inputs[$link->id]) ? "1" : "0";
//            }
//        }

        foreach ($string_langs as $link) {
            if ($link->parent_base->type_is_boolean()) {
                // Проверка нужна
                $base_link_right = GlobalController::base_link_right($link, $role);
                if ($base_link_right['is_edit_link_update'] == false) {
                    continue;
                }
                // похожая формула выше (в этой же процедуре)
                // у этой команды два предназначения:
                // 1) заменить "on" на "1" при отмеченном checkbox
                // 2) создать новый ([$link->id]-й) элемент массива со значением "0" при выключенном checkbox
                // в базе данных информация хранится как "0" или "1"
                $inputs[$link->id] = isset($inputs[$link->id]) ? "1" : "0";
            }
        }

        $array_mess = array();

        foreach ($string_langs as $link) {
            if ($link->parent_is_parent_related == false) {
                // Тип - изображение
                if ($link->parent_base->type_is_image() || $link->parent_base->type_is_document()) {
                    // Проверка на обязательность ввода
                    if ($link->parent_base->is_required_lst_num_str_txt_img_doc == true) {
                        $item_seek = MainController::get_parent_item_from_main($item->id, $link->id);
                        $check = false;
                        if ($item_seek) {
                            if (!$item_seek->img_doc_exist()) {
                                $check = true;
                            }
                        } else {
                            $check = true;
                        }

                        $errors = false;
                        if ($check && !$request->hasFile($link->id)) {
                            $array_mess[$link->id] = trans('main.is_required_lst_num_str_txt_img_doc') . '!';
                            $errors = true;
                        }
                        if ($errors) {
                            // повторный вызов формы
                            return redirect()->back()
                                ->withInput()
                                ->withErrors($array_mess);
                        }
                    }
                }
            }
        }

        foreach ($inputs as $key => $value) {
            $inputs[$key] = ($value != null) ? $value : "";
        }
        $strings_inputs = $request->only($string_names);
        foreach ($strings_inputs as $key => $value) {
            $strings_inputs[$key] = ($value != null) ? $value : "";
        }

        $keys = array_keys($inputs);
        $values = array_values($inputs);

        // Проверка полей с типом "текст" на длину текста
        $errors = false;
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            $work_base = $link->parent_base;
            if ($work_base->type_is_text() && $work_base->length_txt > 0) {
                $errors = false;
                $name_lang_value = null;
                $name_lang_key = null;
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if (($work_base->is_one_value_lst_str_txt == true && $lang_key == 0) || ($work_base->is_one_value_lst_str_txt == false)) {
                        if ($i == 0) {
                            $name_lang_key = $key;
                            $name_lang_value = $value;
                        }
                        // начиная со второго(индекс==1) элемента массива языков учитывать
                        if ($i > 0) {
                            $name_lang_key = $key . '_' . $lang_key;
                            $name_lang_value = $strings_inputs[$name_lang_key];
                        }
                        if (strlen($name_lang_value) > $work_base->length_txt) {
                            $array_mess[$name_lang_key] = trans('main.length_txt_rule') . ' ' . $work_base->length_txt . '!';
                            $errors = true;
                        }
                        $i = $i + 1;
                    }
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
            }
        }

        $errors = false;
        foreach ($inputs as $key => $value) {
            $link = Link::findOrFail($key);
            $work_base = $link->parent_base;
            // при типе "логический" проверять на обязательность заполнения не нужно
            $control_required = false;
            // Тип - список
            if ($work_base->type_is_list()) {
                // так не использовать
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str_txt_img_doc == true) {
                    $control_required = true;
                }
                // это правильно
                //$control_required = true;
            } // Тип - число
            elseif ($work_base->type_is_number()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str_txt_img_doc == true) {
                    $control_required = true;
                }
            } // Тип - строка или текст
            elseif ($work_base->type_is_string() || $work_base->type_is_text()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str_txt_img_doc == true) {
                    $control_required = true;
                }
            } // Тип - дата
            elseif ($work_base->type_is_date()) {
                $control_required = true;
            }

            // при типе корректировки поля "строка", "логический" проверять на обязательность заполнения не нужно
            if ($control_required == true) {
                // Тип - строка или текст
                if ($work_base->type_is_string() || $work_base->type_is_text()) {
                    // поиск в таблице items значение с таким же названием и base_id
                    $name_lang_value = null;
                    $name_lang_key = null;
                    $i = 0;
                    foreach (config('app.locales') as $lang_key => $lang_value) {
                        if (($work_base->is_one_value_lst_str_txt == true && $lang_key == 0) || ($work_base->is_one_value_lst_str_txt == false)) {
                            if ($i == 0) {
                                $name_lang_key = $key;
                                $name_lang_value = $value;
                            }
                            // начиная со второго(индекс==1) элемента массива языков учитывать
                            if ($i > 0) {
                                $name_lang_key = $key . '_' . $lang_key;
                                $name_lang_value = $strings_inputs[$name_lang_key];
                            }
                            // "<option value = '0'>" присваивается при заполнении 'edit.blade' если нет данных (объектов по заданному base)            if ($value == 0)
                            // "$value === '0'" использовать для точного сравнения (например, при $link->parent_base->type_is_string())
                            // Преобразование null в '' было ранее произведено
                            if ($name_lang_value == "") {
                                $array_mess[$name_lang_key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                                $errors = true;
                            }

                            $i = $i + 1;
                        }
                    }
                } else {
                    // "<option value = '0'>" присваивается при заполнении 'edit.blade' если нет данных (объектов по заданному base)            if ($value == 0)
                    // "$value === '0'" использовать для точного сравнения (например, при $link->parent_base->type_is_string())
                    if ($value == null) {
                        $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                        $errors = true;
                    } elseif ($value === '0') {
                        $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                        $errors = true;
                    } else {
                        $floatvalue = floatval($value);
                        if ($floatvalue == 0) {
                            $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                            $errors = true;
                        }
                    }
                }
            }
            // Проверка полей с типом "текст" на наличие запрещенных тегов HTML
            if ($work_base->type_is_text()) {
                // поиск в таблице items значение с таким же названием и base_id
                $name_lang_value = null;
                $name_lang_key = null;
                $i = 0;
                foreach (config('app.locales') as $lang_key => $lang_value) {
                    if ($i == 0) {
                        $name_lang_key = $key;
                        $name_lang_value = $value;
                    }
                    if ($link->parent_base->is_one_value_lst_str_txt == false) {
                        // начиная со второго(индекс==1) элемента массива языков учитывать
                        if ($i > 0) {
                            $name_lang_key = $key . '_' . $lang_key;
                            $name_lang_value = $strings_inputs[$name_lang_key];
                        }
                    }
                    $text_html_check = GlobalController::text_html_check($name_lang_value);
                    if ($text_html_check['result'] == true) {
                        $array_mess[$name_lang_key] = $text_html_check['message'] . '!';
                        $errors = true;
                    }
                    $i = $i + 1;
                }
            }
        }
        if ($errors) {
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        // Одно значение у всех языков
        if ($item->base->is_one_value_lst_str_txt == true) {
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }

        // сохранение предыдущих значений $array_plan
        // до начала выполнения транзакции
        $array_calc = $this->get_array_calc_edit($item)['array_calc'];
        try {
            // начало транзакции
            // $array_plan передается при корректировке
            // $del_links ипользуется при корректировке item (функция ext_update()), при добавлении не используется (функция ext_store())
            DB::transaction(function ($r) use ($item, $it_texts, $keys, $values, $strings_inputs, $del_links) {

                //$item->save();

                // тип - текст
                if ($it_texts) {
                    if ($item->base->type_is_text()) {
                        //$text = $item->text();
                        $text = Text::where('item_id', $item->id)->first();
                        if (!$text) {
                            $text = new Text();
                            $text->item_id = $item->id;
                        }
                        $text->name_lang_0 = $it_texts['name_lang_0'];
                        // Одно значение у всех языков для тип - текст
                        if ($item->base->is_one_value_lst_str_txt == true) {
                            $text->name_lang_1 = $text->name_lang_0;
                            $text->name_lang_2 = $text->name_lang_0;
                            $text->name_lang_3 = $text->name_lang_0;
                        } else {
                            $text->name_lang_1 = "";
                            $text->name_lang_2 = "";
                            $text->name_lang_2 = "";
                            foreach ($it_texts as $it_key => $it_text) {
                                $text[$it_key] = $it_texts[$it_key];
                            }
                        }
                        $text->save();
                    }
                }
                // Удаление изображений/документов с проставленной отметкой об удалении
                foreach ($del_links as $key) {
                    $main = Main::where('child_item_id', $item->id)->where('link_id', $key)->first();
                    if ($main) {
                        $main->parent_item->delete();
                        $main->delete();
                    }
                }

                // только для ext_update()
                // true - с реверсом
                $this->save_info_sets($item, true);

                // после ввода данных в форме массив состоит:
                // индекс массива = link_id (для занесения в links->id)
                // значение массива = item_id (для занесения в mains->parent_item_id)
                $i_max = count($keys);

                // Новый вариант
                // Сначала проверка, потом присвоение
                // Проверка на $main->link_id, если такой не найден - то удаляется
                $mains = Main::where('child_item_id', $item->id)->get();
                foreach ($mains as $main) {
                    $delete_main = false;
                    $link = Link::where('id', $main->link_id)->first();
                    if ($link) {
                        if ($link->child_base_id != $item->base_id) {
                            $delete_main = true;
                        }
                    } else {
                        $delete_main = true;
                    }
                    if ($delete_main) {
                        $main->delete();
                    }
                }

                // Присвоение данных
                // "$i = 0" использовать, т.к. индексы в массивах начинаются с 0
                $i = 0;
                $valits = $values;

                foreach ($keys as $key) {
                    $main = Main::where('child_item_id', $item->id)->where('link_id', $key)->first();
                    if ($main == null) {
                        $main = new Main();
                        // при создании записи "$item->created_user_id" заполняется
                        $main->created_user_id = Auth::user()->id;
                    } else {
                        // удалить файл-предыдущее значение при корректировке
                        if ($main->parent_item->base->type_is_image() || $main->parent_item->base->type_is_document()) {
                            if ($values[$i] != "") {
                                Storage::delete($main->parent_item->filename());
                                //$main->parent_item->delete();
                            }
                        }
                    }
                    $this->save_main($main, $item, $keys, $values, $valits, $i, $strings_inputs);
                    // "$i = $i + 1;" использовать здесь, т.к. индексы в массивах начинаются с 0
                    $i = $i + 1;
                }

                $rs = $this->calc_value_func($item);
                if ($rs != null) {
                    $item->name_lang_0 = $rs['calc_lang_0'];
                    $item->name_lang_1 = $rs['calc_lang_1'];
                    $item->name_lang_2 = $rs['calc_lang_2'];
                    $item->name_lang_3 = $rs['calc_lang_3'];
                }
                // ext_update()
                // При reverse = false передаем null
                $this->save_sets($item, $keys, $values, $valits, false);

                $item->save();

            }, 3);  // Повторить три раза, прежде чем признать неудачу
            // окончание транзакции

        } catch (Exception $exc) {
            return trans('transaction_not_completed') . ": " . $exc->getMessage();
        }

        // удаление неиспользуемых данных
        $this->delete_items_old($array_calc);

        if (env('MAIL_ENABLED') == 'yes') {
            $base_right = GlobalController::base_right($item->base, $role);
            if ($base_right['is_edit_email_base_update'] == true) {
                $email_to = $item->created_user->email;
                $appname = config('app.name', 'Abakus');
                try {
                    Mail::send(['html' => 'mail/item_update'], ['item' => $item],
                        function ($message) use ($email_to, $appname, $item) {
                            $message->to($email_to, '')->subject(trans('main.edit_record') . ' - ' . $item->base->name());
                            $message->from(env('MAIL_FROM_ADDRESS', ''), $appname);
                        });
                } catch (Exception $exc) {
                    return trans('error_sending_email') . ": " . $exc->getMessage();
                }
            }
        }

//        if (env('MAIL_ENABLED') == 'yes'){
//            $appname = config('app.name', 'Abakus');
//            Mail::send(['html' => 'mail/login_site'], ['remote_addr' => $_SERVER['REMOTE_ADDR'],
//                'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],'appname' => $appname],
//                function ($message) use ($appname) {
//                    $message->to('s_astana@mail.ru', '')->subject("Заказ одобрен '" . $appname . "'");
//                    $message->from(env('MAIL_FROM_ADDRESS', 'support@rsb0807.kz'), $appname);
//                });
//        }

        return redirect()->route('item.base_index', ['base' => $item->base, 'project' => $item->project, 'role' => $role]);

    }

    private
    function delete_items_old($array_calc)
    {
        foreach ($array_calc as $key => $value) {
            // использовать '$link = Link::find($key); if($link){}'
            // не использовать findOrFail($key), т.к. данная функция выполняется уже вне основной транзакции
            // и за время окончания выполнения основной транзакции
            // в базе данных (bases, links, items, mains) могут поменяться значения/записи
            //
            // проверка предыдущего значения (числа),
            // и если оно не используется (на него нет parent-ссылок в таблице main)
            // то тогда, это число (запись item) удаляется из таблицы items,
            // чтобы не засорять таблицу items неиспользуемой информацией
            //
            $link = Link::find($key);
            if ($link) {
                // тип корректировки поля - не список
                // '!' используется
                if (!$link->parent_base->type_is_list()) {
                    $item_old_id = $array_calc[$link->id];
                    // выполнять проверку на null,
                    // т.к. в функции get_array_plan() по умолчанию элементам массива $array_calc[] присваивается null
                    if ($item_old_id != null) {
                        $item_old = Item::find($item_old_id);
                        if ($item_old) {
                            if (count($item_old->parent_mains) == 0) {
                                // Проверку на присваивания - ?
                                $item_old->delete();
                            }
                        }
                    }
                }
            }
        }
    }

    function edit(Item $item)
    {
        return view('item/edit', ['item' => $item, 'bases' => Base::all()]);
    }

    function ext_edit(Item $item, Role $role, Link $par_link = null, Item $parent_item = null)
    {
        if (GlobalController::check_project_user($item->project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        $arrays = $this->get_array_calc_edit($item, $par_link, $parent_item);
        $array_calc = $arrays['array_calc'];
        $array_disabled = $arrays['array_disabled'];
        if ($item->code == "" && $item->base->is_code_needed == false) {
            // Похожая строка есть и в ext_create
            $item->code = uniqid($item->base_id . '_', true);
        }

        return view('item/ext_edit', ['base' => $item->base, 'item' => $item,
            'role' => $role,
            'array_calc' => $array_calc,
            'array_disabled' => $array_disabled,
            'par_link' => $par_link, 'parent_item' => $parent_item]);
    }

    function ext_delete_question(Item $item, Role $role, $heading = false)
    {
        if (GlobalController::check_project_user($item->project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

        return view('item/ext_show', ['type_form' => 'delete_question', 'item' => $item, 'role' => $role,
            'array_calc' => $this->get_array_calc_edit($item)['array_calc'], 'heading' => $heading]);
    }

    function ext_delete(Item $item, Role $role, $heading = false)
    {
        if (GlobalController::check_project_user($item->project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }

//        if ($item->base->type_is_image() || $item->base->type_is_document()) {
//            Storage::delete($item->filename());
//        }
//
//        $mains = Main::where('child_item_id', $item->id)->get();
//        foreach ($mains as $main) {
//            if ($main->parent_item->base->type_is_image() || $main->parent_item->base->type_is_document()) {
//                Storage::delete($main->parent_item->filename());
//                $main->parent_item->delete();
//            }
//        }
        if (self::is_delete($item, $role) == true) {

            $item_copy = $item;

            if ($this->is_save_sets($item)) {

                try {
                    // начало транзакции
                    DB::transaction(function ($r) use ($item) {
                        // true - с реверсом
                        $this->save_info_sets($item, true);

                        $item->delete();

                    }, 3);  // Повторить три раза, прежде чем признать неудачу
                    // окончание транзакции

                } catch (Exception $exc) {
                    return trans('transaction_not_completed') . ": " . $exc->getMessage();
                }

            } else {
                $item->delete();

            }

            $item = $item_copy;
            if (env('MAIL_ENABLED') == 'yes') {
                $base_right = GlobalController::base_right($item->base, $role);
                if ($base_right['is_show_email_base_delete'] == true) {
                    $email_to = $item->created_user->email;
                    $deleted_user_date_time = GlobalController::deleted_user_date_time();
                    $appname = config('app.name', 'Abakus');
                    try {
                        Mail::send(['html' => 'mail/item_delete'], ['item' => $item, 'deleted_user_date_time' => $deleted_user_date_time],
                            function ($message) use ($email_to, $appname, $item) {
                                $message->to($email_to, '')->subject(trans('main.delete_record') . ' - ' . $item->base->name());
                                $message->from(env('MAIL_FROM_ADDRESS', ''), $appname);
                            });
                    } catch (Exception $exc) {
                        return trans('error_sending_email') . ": " . $exc->getMessage();
                    }
                }
            }

        }
        //return $heading == true ? redirect()->route('item.base_index', $item->base_id) : redirect(session('base_index_previous_url'));
        if ($heading == true) {
            return redirect()->route('item.base_index', $item->base_id);
        } else {
            if (Session::has('base_index_previous_url')) {
                return redirect(session('base_index_previous_url'));
            } else {
                return redirect()->back();
            }
        }
    }

    static function is_delete(Item $item, Role $role)
    {
        // Нужно "$result = false;"
        $result = false;
        $base_right = GlobalController::base_right($item->base, $role);
        if ($base_right['is_list_base_delete'] == true) {
            if ($base_right['is_list_base_used_delete'] == true) {
                $result = true;
            } else {
                // Отрицание "!" используется
                $result = !self::main_exists($item);
            }
        }
        return $result;
    }

    static function main_exists(Item $item)
    {
        return Main::where('parent_item_id', $item->id)->exists();
    }

// Функции get_items_for_link() и get_items_ext_edit_for_link()
// в целом похожи в части возвращаемых 'result_parent_label', 'result_parent_base_name', 'result_parent_base_items'
    static function get_items_for_link(Link $link, Project $project, Role $role)
    {
        $result_parent_label = '';
        $result_child_base_name = '';
        $result_parent_base_name = '';
        $result_child_base_items = [];
        $result_parent_base_items_no_get = [];
        $result_parent_base_items = [];
        $result_child_base_items_options = '';
        $result_parent_base_items_options = '';

        if ($link != null) {
            // наименование
            $result_parent_label = $link->parent_label();
            // наименование child_base и parent_base
            $result_child_base_name = $link->child_base->name();
            $result_parent_base_name = $link->parent_base->name();
//            // если это фильтрируемое поле - то, тогда загружать весь список не нужно
//            $link_exists = Link::where('parent_is_child_related', true)->where('parent_child_related_start_link_id', $link->id)->exists();
//            if ($link_exists == null) {
            $name = "";  // нужно, не удалять
            $index = array_search(App::getLocale(), config('app.locales'));
            if ($index !== false) {   // '!==' использовать, '!=' не использовать
                $name = 'name_lang_' . $index;
            }

            // список items по выбранному child_base_id
            $result_child_base_items = Item::select(['id', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'])
                ->where('base_id', $link->child_base_id)->where('project_id', $project->id)->orderBy($name)
                ->get();
            foreach ($result_child_base_items as $item) {
                //$item->name() - для быстрого получения $item->name()
                $result_child_base_items_options = $result_child_base_items_options . "<option value='" . $item->id . "'>" . $item->name() . "</option>";
            }

            // список items по выбранному parent_base_id
            $base_right = GlobalController::base_right($link->parent_base, $role);

            // если это фильтрируемое поле - то, тогда загружать весь список не нужно
            $link_exists = Link::where('parent_is_child_related', true)->where('parent_child_related_start_link_id', $link->id)->exists();
            //dd($link_exists);
            //if ($link_exists == false || $link_exists == null) {
            //if ($link_exists == true) {
            //dd($link->parent_is_in_the_selection_list_use_the_calculated_table_field);
            // 1.0 В списке выбора использовать поле вычисляемой таблицы
            //dd($link->parent_is_in_the_selection_list_use_the_calculated_table_field);
            if ($link->parent_is_in_the_selection_list_use_the_calculated_table_field == true) {
                $set = Set::findOrFail($link->parent_selection_calculated_table_set_id);
                $set_link = $set->link_to;
                // Получаем список из вычисляемой таблицы
                $result_parent_base_items = Item::select(DB::Raw('items.*'))
                    ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                    ->where('mains.link_id', '=', $set_link->id);

                //->orderBy('items.' . $name);

                //                             ->where('items.project_id', $project->id)

//                    1.1 В списке выбора использовать дополнительное связанное поле вычисляемой таблицы
                if ($link->parent_is_use_selection_calculated_table_link_id_0 == true) {
                    $link_id = $link->parent_selection_calculated_table_link_id_0;
                    // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
                    // Список 'items.*' формируется из 'mains.parent_item_id'
                    // Связь с вычисляемой таблицей - 'joinSub($result_parent_base_items, 'items_start', function ($join) {
                    //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
                    $result_parent_base_items = Item::select(DB::Raw('items.*'))
                        ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                        ->joinSub($result_parent_base_items, 'items_start', function ($join) {
                            $join->on('mains.child_item_id', '=', 'items_start.id');
                        })
                        ->where('mains.link_id', '=', $link_id)
                        ->distinct()
                        ->orderBy('items.' . $name);

                    //                             ->where('items.project_id', $project->id)

//                        1.2 В списке выбора использовать два дополнительных связанных поля вычисляемой таблицы
                    if ($link->parent_is_use_selection_calculated_table_link_id_1 == true) {
                        $link_id = $link->parent_selection_calculated_table_link_id_1;
                        // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
                        // Список 'items.*' формируется из 'mains.parent_item_id'
                        // Связь с таблицей-результатом предыдущего запроса - 'joinSub($result_parent_base_items, 'items_start', function ($join) {
                        //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
                        $result_parent_base_items = Item::select(DB::Raw('items.*'))
                            ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                            ->joinSub($result_parent_base_items, 'items_start', function ($join) {
                                $join->on('mains.child_item_id', '=', 'items_start.id');
                            })
                            ->where('mains.link_id', '=', $link_id)
                            ->distinct()
                            ->orderBy('items.' . $name);

                        //                             ->where('items.project_id', $project->id)
                    }
                }
                // Загрузить список $items
            } else {
                $result_parent_base_items = Item::select(['id', 'code', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3', 'created_user_id'])
                    ->where('base_id', $link->parent_base_id)
                    ->where('project_id', $project->id);
//                        ->orderBy($name);
                //dd($result_parent_base_items->get());
            }
            // Такая же проверка и в GlobalController (function items_right()),
            // в ItemController (function browser(), get_items_for_link(), get_items_ext_edit_for_link())
            if ($base_right['is_list_base_byuser'] == true) {
                $result_parent_base_items = $result_parent_base_items->where('created_user_id', GlobalController::glo_user_id());
            }
            $result_parent_base_items_no_get = $result_parent_base_items;
            //dd($result_parent_base_items_no_get);
            // '->get()' нужно
            $result_parent_base_items = $result_parent_base_items->get();
            //dd($result_parent_base_items);
            foreach ($result_parent_base_items as $item) {
                $result_parent_base_items_options = $result_parent_base_items_options . "<option value='" . $item->id . "'>" . $item->name() . "</option>";
            }
            //} else {

            //}
        }
        return [
            'result_parent_label' => $result_parent_label,
            'result_child_base_name' => $result_child_base_name,
            'result_parent_base_name' => $result_parent_base_name,
            'result_child_base_items' => $result_child_base_items,
            'result_parent_base_items_no_get' => $result_parent_base_items_no_get,
            'result_parent_base_items' => $result_parent_base_items,
            'result_child_base_items_options' => $result_child_base_items_options,
            'result_parent_base_items_options' => $result_parent_base_items_options,
        ];
    }

// Функции get_items_for_link() и get_items_ext_edit_for_link()
// в целом похожи в части возвращаемых 'result_parent_label', 'result_parent_base_name', 'result_parent_base_items'
    static function get_items_ext_edit_for_link(Link $link, Project $project, Role $role)
    {
        // наименование
        $result_parent_label = $link->parent_label();
        $result_parent_base_name = $link->parent_base->name();
        $result_parent_base_items = [];
        // Такая же проверка ItemController::get_items_ext_edit_for_link(),
        // в ext_edit.php
        if ($link->parent_base->type_is_list()) {
            $name = "";  // нужно, не удалять
            $index = array_search(App::getLocale(), config('app.locales'));
            if ($index !== false) {   // '!==' использовать, '!=' не использовать
                $name = 'name_lang_' . $index;
            }
            // список items по выбранному parent_base_id
            $base_right = GlobalController::base_right($link->parent_base, $role);
            $result_parent_base_items = Item::select(['id', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3', 'created_user_id'])->where('base_id', $link->parent_base_id)->where('project_id', $project->id)->orderBy($name);
            // Такая же проверка и в GlobalController (function items_right()),
            // в ItemController (function browser(), get_items_for_link(), get_items_ext_edit_for_link())
            if ($base_right['is_list_base_byuser'] == true) {
                $result_parent_base_items = $result_parent_base_items->where('created_user_id', GlobalController::glo_user_id());
            }
            $result_parent_base_items = $result_parent_base_items->get();
        }
        return [
            'result_parent_label' => $result_parent_label,
            'result_parent_base_name' => $result_parent_base_name,
            'result_parent_base_items' => $result_parent_base_items,
        ];
    }

// Используется в ext_edit.php при фильтрации данных + данные из вычисляемых таблиц
// $item_select - выбранное значение
    static function get_selection_child_items_from_parent_item(Link $link, Item $item_select)
    {
        $result_items_no_get = null;
        $result_items = null;
        $result_items_name_options = null;
        $result_parent_base_items = null;
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
        }
        // Похожие строки есть в LinkController store()/update() и в ItemController get_selection_child_items_from_parent_item()
        // Проверка допустимого случая, если 'Фильтровать поля == true' и '1.0 В списке выбора использовать поле вычисляемой таблицы == true'
        $link_start = Link::findOrFail($link->parent_child_related_start_link_id);
        $link_result = Link::findOrFail($link->parent_child_related_result_link_id);
        // 1.0 В списке выбора использовать поле вычисляемой таблицы
        // 1.1 В списке выбора использовать дополнительное связанное поле вычисляемой таблицы
        if ($link->parent_is_in_the_selection_list_use_the_calculated_table_field) {
            $set = Set::findOrFail($link->parent_selection_calculated_table_set_id);
            $set_link = $set->link_to;
            // Получаем список из вычисляемой таблицы
            $result_parent_base_items = Item::select(DB::Raw('items.*'))
                ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                ->where('mains.link_id', '=', $set_link->id)
                ->orderBy('items.' . $name);
            $sel_error = true;
            if ($link->parent_is_use_selection_calculated_table_link_id_0) {
                $set = Set::findOrFail($link->parent_selection_calculated_table_set_id);
                $link_sel_0 = Link::findOrFail($link->parent_selection_calculated_table_link_id_0);
                // 1.1 В списке выбора использовать дополнительное связанное поле вычисляемой таблицы
                if ($link->parent_is_use_selection_calculated_table_link_id_1 == false) {
                    $sel_error = !(($set->link_to->parent_base_id == $link_start->parent_base_id) && ($link_sel_0->parent_base_id == $link_result->parent_base_id));

                    if ($sel_error == false) {
                        $link_id = $link->parent_selection_calculated_table_link_id_0;
                        // '$result_child_base_items' присваивается
                        // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
                        // Список 'items.*' формируется из 'mains.child_item_id'
                        // Фильтр используется '->where('mains.parent_item_id', '=', $item_select->id)'
                        // Связь с вычисляемой таблицей - 'joinSub($result_parent_base_items, 'items_start', function ($join) {
                        //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
                        //  Нужно '->join('mains', 'items.id', '=', 'mains.child_item_id')'
                        $result_child_base_items = Item::select(DB::Raw('items.*'))
                            ->join('mains', 'items.id', '=', 'mains.child_item_id')
                            ->joinSub($result_parent_base_items, 'items_start', function ($join) {
                                $join->on('mains.child_item_id', '=', 'items_start.id');
                            })
                            ->where('mains.link_id', '=', $link_id)
                            ->where('mains.parent_item_id', '=', $item_select->id)
                            ->distinct()
                            ->orderBy('items.' . $name);;
                    }

                } //                Т.е. '$link->parent_is_use_selection_calculated_table_link_id_1 == true'
                else {

                    $link_sel_1 = Link::findOrFail($link->parent_selection_calculated_table_link_id_1);
                    $sel_error = !(($link_sel_0->parent_base_id == $link_start->parent_base_id) && ($link_sel_1->parent_base_id == $link_result->parent_base_id));

                    if ($sel_error == false) {
                        $link_id = $link->parent_selection_calculated_table_link_id_0;
                        // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
                        // Список 'items.*' формируется из 'mains.parent_item_id'
                        // Связь с вычисляемой таблицей - 'joinSub($result_parent_base_items, 'items_start', function ($join) {
                        //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
                        $result_parent_base_items = Item::select(DB::Raw('items.*'))
                            ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                            ->joinSub($result_parent_base_items, 'items_start', function ($join) {
                                $join->on('mains.child_item_id', '=', 'items_start.id');
                            })
                            ->where('mains.link_id', '=', $link_id)
                            ->distinct()
                            ->orderBy('items.' . $name);

                        //                             ->where('items.project_id', $project->id)

//                        1.2 В списке выбора использовать два дополнительных связанных поля вычисляемой таблицы
                        $link_id = $link->parent_selection_calculated_table_link_id_1;
                        // '$result_child_base_items' присваивается
                        // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
                        // Список 'items.*' формируется из 'mains.child_item_id'
                        // Фильтр используется '->where('mains.parent_item_id', '=', $item_select->id)'
                        // Связь с вычисляемой таблицей - 'joinSub($result_parent_base_items, 'items_start', function ($join) {
                        //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
                        //  Нужно '->join('mains', 'items.id', '=', 'mains.child_item_id')'

                        $result_child_base_items = Item::select(DB::Raw('items.*'))
                            ->join('mains', 'items.id', '=', 'mains.child_item_id')
                            ->joinSub($result_parent_base_items, 'items_start', function ($join) {
                                $join->on('mains.child_item_id', '=', 'items_start.id');
                            })
                            ->where('mains.link_id', '=', $link_id)
                            ->where('mains.parent_item_id', '=', $item_select->id)
                            ->distinct()
                            ->orderBy('items.' . $name);

                    }
                }
            }
        }

        $result_items_no_get = $result_child_base_items;
        // '->get()' нужно
        $result_items = $result_child_base_items->get();

        if ($result_items) {
            $result_items_name_options = "";
            foreach ($result_items as $item) {
                $result_items_name_options = $result_items_name_options . "<option value='" . $item->id . "'>" . $item->name() . "</option>";
            }
        } else {
            $result_items_name_options = "<option value='0'>" . trans('main.no_information') . "!</option>";
        }

        return ['result_items_no_get' => $result_items_no_get,
            'result_items' => $result_items,
            'result_items_name_options' => $result_items_name_options];
    }

// Используется в ext_edit.php при обычной фильтрации данных
    static function get_child_items_from_parent_item(Base $base_start, Item $item_start, Link $link)
    {
        $link_result = Link::find($link->parent_child_related_result_link_id);
        $result_items = null;
        $result_items_name_options = null;
        $cn = 0;
        $error = false;
        $link = null;
        $mains = null;
        $items_parent = null;
        $items_child = null;
        // список links - маршрутов до поиска нужного объекта
        $links = BaseController::get_array_bases_tree_routes($base_start->id, $link_result->id, false);
        if ($links) {
            $items_parent = array();
            // добавление элемента в конец массива
            array_unshift($items_parent, $item_start->id);
            $cn = 0;
            $error = false;
            foreach ($links as $link_value) {
                $cn = $cn + 1;
                $link = Link::find($link_value);
                if (!$link) {
                    $error = true;
                    break;
                }
                // обнуление массива $items_child
                $items_child = array();
                foreach ($items_parent as $item_id) {
                    // $item используется в цикле
                    $mains = Main::select(['child_item_id'])
                        ->where('parent_item_id', $item_id)->where('link_id', $link->id)->get();
                    if (!$mains) {
                        $error = true;
                        break;
                    }
                    foreach ($mains as $main) {
                        // добавление элемента в конец массива
                        array_unshift($items_child, $main->child_item_id);
                    }
                }
                $items_parent = $items_child;
            }
        }
        if (!$error) {
            // проверки "цикл прошел по всем элементам до конца";
            if (count($links) == $cn) {
                $result_items = $items_child;
                if ($items_child) {
                    $result_items_name_options = "";
                    $selected = false;
                    foreach ($items_child as $item_id) {
                        $item = Item::find($item_id);
                        if ($item) {
//                            $result_items_name_options = $result_items_name_options . "<option value='" . $item_id . "'>" . $item->name() . "</option>";
                            $result_items_name_options = $result_items_name_options . "<option value='" . $item_id;
                            if ($selected) {
                                $result_items_name_options = $result_items_name_options . " selected ";
                            }
                            $result_items_name_options = $result_items_name_options . "'>" . $item->name() . "</option>";
                        }
                    }
                    //$result_items_name_options = $result_items_name_options . "<option value='0'>" . trans('main.no_information') . "!</option>";
                } else {
                    $result_items_name_options = "<option value='0'>" . trans('main.no_information') . "!</option>";
                }
            }
        }
        // }
        return ['result_items' => $result_items,
            'result_items_name_options' => $result_items_name_options];
    }

    static function get_parent_item_from_child_item(Item $item_start, Link $link_result)
    {
        $result_item = null;
        $result_item_name = null;
        $result_item_name_options = null;
        $item = $item_start;
        $cn = 0;
        $error = false;
        $link = null;
        $link_work = null;
        $main = null;
        // список links - маршрутов до поиска нужного объекта
        $links = BaseController::get_array_bases_tree_routes($item_start->base_id, $link_result->id, true);
        if ($links) {
            $cn = 0;
            $error = false;
            foreach ($links as $value) {
                $cn = $cn + 1;
                $link = Link::find($value);
                if (!$link) {
                    $error = true;
                    break;
                }
                $link_work = $link;
                // первый элемент списка mains по выбранному child_base_id и link_id
                // $item используется в цикле
                $main = Main::select(['id', 'child_item_id', 'parent_item_id', 'link_id'])
                    ->where('child_item_id', $item->id)->where('link_id', $link->id)->get()->first();
                if (!$main) {
                    $error = true;
                    break;
                }
                $item = Item::find($main->parent_item_id);
                if (!$item) {
                    $error = true;
                    break;
                }
            }
            if (!$error) {
                // проверки "цикл прошел по всем элементам до конца" и конечный найденный $link_work == необходимому $link_result;
                if ((count($links) == $cn) && ($link_work == $link_result)) {
                    $result_item = $item;
                    $result_item_name = $item->name();
                    $result_item_name_options = "<option value='" . $item->id . "'>" . $item->name() . "</option>";
                }
            }
        }
        return ['result_item' => $result_item,
            'result_item_name' => $result_item_name,
            'result_item_name_options' => $result_item_name_options];
    }

// Функция get_parent_item_from_calc_child_item() ищет вычисляемое поля от первого невычисляемого
// в форме item/ext_edit.php
// Например: значение вычисляемого (через "Бабушка со стороны матери") "Прабабушка со стороны матери" находится от значение поля "Мать",
// т.е. не зависит от промежуточных значений ("Бабушка со стороны матери")
    static function get_parent_item_from_calc_child_item(Item $item_start, Link $link_result, $item_calc)
    {
        $result_item = null;
        $result_item_id = null;
        $result_item_name = null;
        $result_item_name_options = null;
        // проверка, если link - вычисляемое поле
        if ($link_result->parent_is_parent_related == true) {
            // Не использовать - не работает при сложных связях: Например: Товар-ЕдиницаИзмерения-Цвет
            // ----------------------------------------
            // Вставка нового алгоритма
            // Вычисляем первоначальный $item;
//            $item = null;
//            if ($item_calc == true) {
//                // Поиск item-start (например: в заказе - поиск товара)
//                $item = MainController::get_parent_item_from_main($item_start->id, $link_result->parent_parent_related_start_link_id);
//            } else {
//                $item = $item_start;
//            }
//            if ($item) {
//                // Поиск item-result (например: в товаре - поиск наименования)
//                $item = MainController::get_parent_item_from_main($item->id, $link_result->parent_parent_related_result_link_id);
//                if ($item) {
//                    $result_item = $item;
//                    $result_item_id = $item->id;
//                    if ($item->base->type_is_image() || $item->base->type_is_document()) {
//                        //$result_item_name = "<a href='" . Storage::url($item->filename()) . "'><img src='" . Storage::url($item->filename()) . "' height='50' alt='' title='" . $item->filename() . "'></a>";
//                        if ($item->base->type_is_image()) {
//                            $result_item_name = "<img src='" . Storage::url($item->filename()) . "' height='250' alt='' title='" . $item->title_img() . "'>";
//                        } else {
//                            $result_item_name = "<a href='" . Storage::url($item->filename()) . "'><img src='" . Storage::url($item->filename()) . "' height='50' alt='' title='" . $item->filename() . "'></a>";
//                        }
//                    } elseif ($item->base->type_is_text()) {
//                        $result_item_name = GlobalController::it_txnm_n2b($item);
//                    } else {
//                        // $numcat = false - не выводить числовых поля с разрядом тысячи/миллионы/миллиарды
//                        $result_item_name = $item->name();
//                    }
//                    $result_item_name_options = "<option value='" . $item->id . "'>" . $item->name() . "</option>";
//                }
//            }
            // ------------------------------------------------------------

            // ------------------------------------------------------------
            // Не удалять - сложный алгоритм поиска, например, прабабушка мамы
//            if (1 == 2) {
            // возвращает маршрут $link_ids по вычисляемым полям до первого найденного постоянного link_id ($const_link_id_start)
            $rs = LinkController::get_link_ids_from_calc_link($link_result);
            $const_link_id_start = $rs['const_link_id_start'];
            $link_ids = $rs['link_ids'];
            // Вычисляем первоначальный $item;
            if ($item_calc == true) {
                $item = MainController::get_parent_item_from_main($item_start->id, $const_link_id_start);
            } else {
                $item = $item_start;
            }
            if ($item) {
                if ($const_link_id_start && $link_ids) {
                    $error = false;
                    // цикл по вычисляемым полям
                    foreach (@$link_ids as $link_id) {
                        $link_find = Link::find($link_id);
                        if (!$link_find) {
                            $error = true;
                            break;
                        }
                        $link_find = Link::find($link_find->parent_parent_related_result_link_id);
                        if (!$link_find) {
                            $error = true;
                            break;
                        }
                        // используется поле link->parent_parent_related_result_link_id
                        // находим новый $item (невычисляемый)
                        // $item меняется внутри цикла
                        $item = self::get_parent_item_from_child_item($item, $link_find)['result_item'];
                        if (!$item) {
                            $error = true;
                            break;
                        }
                    }
                    if (!$error && $item) {
                        $result_item = $item;
                        $result_item_id = $item->id;
                        if ($item->base->type_is_image() || $item->base->type_is_document()) {
                            //$result_item_name = "<a href='" . Storage::url($item->filename()) . "'><img src='" . Storage::url($item->filename()) . "' height='50' alt='' title='" . $item->filename() . "'></a>";
                            if ($item->base->type_is_image()) {
                                $result_item_name = "<img src='" . Storage::url($item->filename()) . "' height='250' alt='' title='" . $item->title_img() . "'>";
                            } else {
                                $result_item_name = "<a href='" . Storage::url($item->filename()) . "'><img src='" . Storage::url($item->filename()) . "' height='50' alt='' title='" . $item->filename() . "'></a>";
                            }
                        } elseif ($item->base->type_is_text()) {
                            $result_item_name = GlobalController::it_txnm_n2b($item);
                        } else {
                            // $numcat = false - не выводить числовых поля с разрядом тысячи/миллионы/миллиарды
                            $result_item_name = $item->name();
                        }
                        $result_item_name_options = "<option value='" . $item->id . "'>" . $item->name() . "</option>";
                    }
                }
            }
            //}
            // --------------------------------------------------------------
        }

        return ['result_item' => $result_item,
            'result_item_id' => $result_item_id,
            'result_item_name' => $result_item_name,
            'result_item_name_options' => $result_item_name_options];
    }

    static function form_parent_coll_hier($item_id, $role)
    {
        $item = Item::find($item_id);
        $items = array();
        $result = self::form_parent_hier_coll_start($items, $item_id, 0, $role);
        if ($result != "") {
            $kod = 0;
            $result = '<a data-toggle="collapse" href="#collapse' . $kod . '">' . trans('main.ancestors') . '</br>' .
                '' . '</a>' .
                '<span id="collapse' . $kod . '" class="collapse in">' . $result . '</span>' .
                '<hr>';
        }
        return $result . '';
    }

// $items нужно - чтобы не было бесконечного цикла
//static function form_parent_coll_hier_start($items, $item_id, $level, $role)   - можно использовать так
//static function form_parent_coll_hier_start(&$items, $item_id, $level, $role)  - и так - результаты разные
    static function form_parent_hier_coll_start(&$items, $item_id, $level, $role)
    {
        $result = '';
        $level = $level + 1;
        $item = Item::findOrFail($item_id);
        $base = Base::findOrFail($item->base_id);
        $base_right = GlobalController::base_right($base, $role);
        if ($base_right['is_hier_base_enable'] == true) {
            $mains = Main::all()->where('child_item_id', $item_id)->sortBy(function ($row) {
                return $row->link->parent_base_number;
            });
            if (count($mains) == 0) {
                return '';
            }
            if (!(array_search($item_id, $items) === false)) {
                return '';
            }
            $items[count($items)] = $item_id;
            foreach ($mains as $main) {
                $str = '';
                $link = Link::findOrFail($main->link_id);
                // '$base_link_right = GlobalController::base_link_right($link, $role, false);' false нужно
                $base_link_right = GlobalController::base_link_right($link, $role, false);
                if ($base_link_right['is_hier_link_enable'] == true) {
                    $str = self::form_parent_hier_coll_start($items, $main->parent_item_id, $level, $role);
                    $alink = '';
                    if ($base_link_right['is_list_base_calc'] == true) {
                        $alink = '<a href="' . route('item.ext_show', ['item' => $main->parent_item_id, 'role' => $role]) . '" title="' .
                            $main->parent_item->name() . '">...</a>';
                    }
                    $img_doc = '';
                    if ($link->parent_base->type_is_image()) {
                        $img_doc = GlobalController::view_img($main->parent_item, "small", false, true, false, "");
                    } elseif ($link->parent_base->type_is_document()) {
                        $img_doc = GlobalController::view_doc($main->parent_item);
                    }
                    if ($str == '') {
                        $result = $result . '<li>';
                        if ($img_doc != '') {
                            $result = $result . $main->link->parent_label() . ': ' . '<b>' . $img_doc . '</b>';
                        } else {
                            $result = $result . $main->link->parent_label() . ': ' . '<b>' . $main->parent_item->name() . '</b>' . $alink;
                        }
                        $result = $result . '</li>';
                    } else {
                        $kod = $main->parent_item_id;
                        $result = $result . '<li><span id="collapse' . $kod . '" class="collapse in">' . $str . '</span>
                                <a data-toggle="collapse" href="#collapse' . $kod . '">' . $main->link->parent_label() . ': ' . '<b>';
                        if ($img_doc != '') {
                            $result = $result . $img_doc . '</b>';
                        } else {
                            $result = $result . $main->parent_item->name() . '</b>' . $alink;
                        }
                        $result = $result . '</a></li>';
                    }
                }
            }
            if ($result != '') {
                $result = '<ul type="circle">' . $result . '</ul>';
                if ($level > 1) {
                    $result = '<div class="card">' . $result . '</div>';
                }
            }
        }
        return $result;
    }

// $level_one = true, т.е. получить простые родительские поля один первый уровень
// $level_one = false, т.е. получить связанные(со вложенными значениями) родительские поля один первый уровень, на остальных уровнях показать простые и связанные поля
    static function form_parent_deta_hier($item_id, $role, $level_one)
    {
        $item = Item::find($item_id);
        $items = array();
        $result = self::form_parent_hier_deta_start($items, $item_id, 0, $role, $level_one);
        if ($result != '') {
            //$kod = 0 . $level_one;
            if ($level_one == false) {
//                $result = '<a data-toggle="collapse" href="#collapse' . $kod . '">' . trans('main.ancestors') . '</br>' .
//                    '' . '</a>' .
//                    '<span id="collapse' . $kod . '" class="collapse in">' . $result . '</span>' .
//                    '<hr>';
                $result = trans('main.ancestors') . ':<br>' . $result . '<hr>';
            }
        }
        return $result;
    }

// $items нужно - чтобы не было бесконечного цикла
//static function form_parent_hier_deta_start($items, $item_id, $level, $role, $level_one)   - можно использовать так
//static function form_parent_hier_deta_start(&$items, $item_id, $level, $role, $level_one)  - и так - результаты разные
    static function form_parent_hier_deta_start(&$items, $item_id, $level, $role, $level_one)
    {
        $result = '';
        $level = $level + 1;
        $item = Item::findOrFail($item_id);
        $base = Base::findOrFail($item->base_id);
        $base_right = GlobalController::base_right($base, $role);
        if ($base_right['is_hier_base_enable'] == true) {
            $mains = Main::all()->where('child_item_id', $item_id)->sortBy(function ($row) {
                return $row->link->parent_base_number;
            });
            if (count($mains) == 0) {
                return '';
            }
            if (!(array_search($item_id, $items) === false)) {
                return '';
            }
            if ($level_one == true && ($level > 1)) {
                return '';
            }
            $items[count($items)] = $item_id;
            foreach ($mains as $main) {
                $str = '';
                $link = Link::findOrFail($main->link_id);
                // '$base_link_right = GlobalController::base_link_right($link, $role, false);' true нужно
                $base_link_right = GlobalController::base_link_right($link, $role, false);
                if ($base_link_right['is_hier_link_enable'] == true) {
                    // Получить $str - вложенные родительские значения
                    $str = self::form_parent_hier_deta_start($items, $main->parent_item_id, $level, $role, $level_one);
                    $alink = '';
                    if ($base_link_right['is_list_base_calc'] == true) {
                        $alink = '<a href="' . route('item.ext_show', ['item' => $main->parent_item_id, 'role' => $role]) . '" title="' .
                            $main->parent_item->name() . '">...</a>';
                    }
                    $img_doc = '';
                    if ($link->parent_base->type_is_image()) {
                        $img_doc = GlobalController::view_img($main->parent_item, "small", false, true, false, "");
                    } elseif ($link->parent_base->type_is_document()) {
                        $img_doc = GlobalController::view_doc($main->parent_item);
                    }

                    // $link_exists = false, поле $main->parent_item->base_id простое
                    // $link_exists = true, поле $main->parent_item->base_id связанное
                    // Например у Человека/Инструкции простые поля: Фамилия, Имя, Отчество, Дата рождения, Пол, Национальность, Наименование, Документ
                    // связанные поля: Родители, Папка
                    $link_exists = Link::where('child_base_id', $main->parent_item->base_id)->exists();

//                  if (!($level_one == true && ($link_exists))) {
                    if ($level_one == false || !$link_exists) {
                        if ($str == '') {
                            //if (!($level_one == false && $level == 1)) {
                            if ($level_one == true || $level > 1) {
                                $result = $result . '<li>';
                                if ($img_doc != '') {
                                    $result = $result . $main->link->parent_label() . ': ' . '<b>' . $img_doc . '</b>';
                                } else {
                                    $result = $result . $main->link->parent_label() . ': ' . '<b>' . $main->parent_item->name() . '</b>' . $alink;
                                }
                                $result = $result . '</li>';
                            }
                        } else {
                            $result = $result . '<li><details><summary>' . $main->link->parent_label() . ': ' . '<b>';
                            if ($img_doc != '') {
                                $result = $result . $img_doc . '</b>';
                            } else {
                                $result = $result . $main->parent_item->name() . '</b> ' . $alink;
                            }
                            $result = $result . '</summary>' . $str . '</details></li>';
                        }
                    }
                }
            }
            if ($result != '') {
                $result = '<ul type="circle">' . $result . "</ul>";
            }
        }
        return $result;
    }

    static function form_child_deta_hier($item_id, $role)
    {
        $item = Item::find($item_id);
        $items = array();
        $result = self::form_child_hier_deta_start($items, $item_id, 0, $role);
        if ($result != '') {
            $result = trans('main.descendants') . ':<br>' . $result . '<hr>';
        }
        return $result;
    }

// $items нужно - чтобы не было бесконечного цикла
//static function form_child_hier_deta_start($items, $item_id, $level, $role)   - можно использовать так
//static function form_child_hier_deta_start(&$items, $item_id, $level, $role)  - и так - результаты разные
// '$items' и '$items_dop' использовать для того, чтобы записи, отображаемые на экране, были уникальными (см.ниже)
    static function form_child_hier_deta_start(&$items, $item_id, $level, $role)
    {
        $result = '';
        $level = $level + 1;
        $item = Item::findOrFail($item_id);
        $base = Base::findOrFail($item->base_id);
        $base_right = GlobalController::base_right($base, $role);
        if ($base_right['is_hier_base_enable'] == true) {
            $mains = Main::all()->where('parent_item_id', $item_id)->sortBy(function ($row) {
                return $row->child_item->name();
            });
            if (count($mains) == 0) {
                return '';
            }
            if (!(array_search($item_id, $items) === false)) {
                return '';
            }
            $items[count($items)] = $item_id;

            foreach ($mains as $main) {
                $str = '';
                $link = Link::findOrFail($main->link_id);
                // '$base_link_right = GlobalController::base_link_right($link, $role, true);' true нужно
                $base_link_right = GlobalController::base_link_right($link, $role, true);
                if ($base_link_right['is_hier_link_enable'] == true) {
                    // Получить $str - вложенные детские значения
                    $str = self::form_child_hier_deta_start($items, $main->child_item_id, $level, $role);
                    $alink = '';
                    if ($base_link_right['is_list_base_calc'] == true) {
                        $alink = '<a href="' . route('item.ext_show', ['item' => $main->child_item_id, 'role' => $role]) . '" title="' .
                            $main->child_item->name() . '">...</a>';
                    }
                    $img_doc = '';
                    if ($link->child_base->type_is_image()) {
                        $img_doc = GlobalController::view_img($main->child_item, "small", false, true, false, "");
                    } elseif ($link->child_base->type_is_document()) {
                        $img_doc = GlobalController::view_doc($main->child_item);
                    }
                    $items_dop = array();
                    // '$items' и '$items_dop' использовать для того, чтобы записи, отображаемые на экране, были уникальными
                    if ($str == '') {
                        // '$items' использовать
                        // 'level_one = true' используется
                        // получить простые родительские поля один первый уровень
                        $str = self::form_parent_hier_deta_start($items, $main->child_item_id, 0, $role, true);
                    } else {
                        // '$items_dop' использовать
                        // 'level_one = true' используется
                        // получить простые родительские поля один первый уровень
                        // '. $str' используется
                        $str = self::form_parent_hier_deta_start($items_dop, $main->child_item_id, 0, $role, true) . $str;
                    }
                    if ($str == '') {
                        $result = $result . '<li>';
                        if ($img_doc != '') {
                            $result = $result . $main->link->child_label() . ': ' . '<b>' . $img_doc . '</b>';
                        } else {
                            $result = $result . $main->link->child_label() . ': ' . '<b>' . $main->child_item->name() . '</b>' . $alink;
                        }
                        $result = $result . '</li>';
                    } else {
                        $result = $result . '<li><details><summary>' . $main->link->child_label() . ': ' . '<b>';
                        if ($img_doc != '') {
                            $result = $result . $img_doc . '</b>';
                        } else {
                            $result = $result . $main->child_item->name() . '</b> ' . $alink;
                        }
                        $result = $result . '</summary>' . $str . '</details>' . '</li>';
                    }
                }
            }
            if ($result != '') {
                $result = '<ul type="circle">' . $result . "</ul>";
            }
        }
        return $result;
    }

// Функция calc_value_func() вычисляет наименования для записи $item
    function calc_value_func(Item $item, $level = 0, $first_run = true)
    {
        // Эта функция только для base с вычисляемым наименованием
        if ($item->base->is_calcname_lst == false) {
            return null;
        }
        $level = $level + 1;

        $array_calc = $this->get_array_calc_edit($item)['array_calc'];
        $item_find = null;
        $item_result = null;
        $result_func = null;
        $calc_lang_0 = "";
        $calc_lang_1 = "";
        $calc_lang_2 = "";
        $calc_lang_3 = "";
        $is_required_second = false;
        // по циклу значений mains
        foreach ($array_calc as $key => $value) {
            $next = false;
            $link = Link::find($key);
            // Эта строка "$item_result = null;" нужна
            $item_result = null;
            if ($link) {
                // если поле входит в состав вычисляемого составного поля / Для вычисляемого наименования
                if ($link->parent_is_calcname == true) {
                    // $first_run = false запускается только для однородных значений (например: ФизЛицо имеет поле Мать(ФизЛицо), Отец(ФизЛицо))
                    if (($first_run == true) ||
                        (($first_run == false)
                            && (($item->base->is_same_small_calcname == false)
                                || ($item->base->is_same_small_calcname == true) && ($link->parent_is_small_calcname == true)))) {
                        if ($value == null) {
                            // Проверка на вычисляемые поля / Автоматически заполнять из родительского поля ввода
                            if ($link->parent_is_parent_related == true) {
                                $const_link_id_start = LinkController::get_link_ids_from_calc_link($link)['const_link_id_start'];
                                $link_parent = Link::find($link->parent_parent_related_start_link_id);
                                if ($link_parent) {
                                    // Если существует такой индекс в массиве
                                    if (array_key_exists($const_link_id_start, $array_calc)) {
                                        $item_find = Item::find($array_calc[$const_link_id_start]);
                                        if ($item_find) {
                                            // Функция get_parent_item_from_calc_child_item() ищет вычисляемое поля от первого невычисляемого
                                            // Например: значение вычисляемого (через "Бабушка со стороны матери") "Прабабушка со стороны матери" находится от значение поля "Мать",
                                            // т.е. не зависит от промежуточных значений ("Бабушка со стороны матери")
                                            $result_func = self::get_parent_item_from_calc_child_item($item_find, $link, false);
                                            // Сохранить значение в массиве
                                            $array_calc[$link->id] = $result_func['result_item_id'];
                                            $item_result = $result_func['result_item'];
                                        }
                                    }
                                }
                            }
                        } else {
                            $item_result = Item::find($value);
                        }
                    }
                }
                if ($item_result) {
                    $dop_name_0 = "";
                    $dop_name_1 = "";
                    $dop_name_2 = "";
                    $dop_name_3 = "";
                    if ($item->base_id == $item_result->base_id) {
                        if ($level == 1) {
                            // всего два запуска этой функции (основной и этот), только для однородных значений (например: ФизЛицо имеет поле Мать(ФизЛицо), Отец(ФизЛицо))
                            $rs = $this->calc_value_func($item_result, $level, false);
                            $dop_name_0 = $rs['calc_lang_0'] == "" ? "" : $item->base->sepa_same_left_calcname . $rs['calc_lang_0'] . $item->base->sepa_same_right_calcname;
                            $dop_name_1 = $rs['calc_lang_1'] == "" ? "" : $item->base->sepa_same_left_calcname . $rs['calc_lang_1'] . $item->base->sepa_same_right_calcname;
                            $dop_name_2 = $rs['calc_lang_2'] == "" ? "" : $item->base->sepa_same_left_calcname . $rs['calc_lang_2'] . $item->base->sepa_same_right_calcname;
                            $dop_name_3 = $rs['calc_lang_3'] == "" ? "" : $item->base->sepa_same_left_calcname . $rs['calc_lang_3'] . $item->base->sepa_same_right_calcname;
                        } else {
                            continue;
                            //$res_names = $item_result->names();
//                            $dop_name_0 = $res_names[0];
//                            $dop_name_1 = $res_names[1];
//                            $dop_name_2 = $res_names[2];
//                            $dop_name_3 = $res_names[3];
//                            $dop_name_0 = "";
//                            $dop_name_1 = "";
//                            $dop_name_2 = "";
//                            $dop_name_3 = "";
                        }
                    } else {
                        $res_names = $item_result->names();
                        $dop_name_0 = $res_names[0];
                        $dop_name_1 = $res_names[1];
                        $dop_name_2 = $res_names[2];
                        $dop_name_3 = $res_names[3];
                    }
                    $dop_name_0 = trim($dop_name_0);
                    $dop_name_1 = trim($dop_name_1);
                    $dop_name_2 = trim($dop_name_2);
                    $dop_name_3 = trim($dop_name_3);
                    if (!($dop_name_0 == "" && $dop_name_1 == "" && $dop_name_2 == "" && $dop_name_3 == "")) {
                        // $item->base->sepa_calcname - символ разделения для вычисляемых полей
                        // "\~" - символ перевода каретки (используется также в Item.php: функции name() nmbr())
                        // "\~" - символ перевода каретки (используется также в ItemController.php: функция calc_value_func())
                        $sc = trim($item->base->sepa_calcname) . "\~";
//                        $dop_sepa0 = $calc_lang_0 == "" ? "" : $sc . " ";
//                        $dop_sepa1 = $calc_lang_1 == "" ? "" : $sc . " ";
//                        $dop_sepa2 = $calc_lang_2 == "" ? "" : $sc . " ";
//                        $dop_sepa3 = $calc_lang_3 == "" ? "" : $sc . " ";
                        $dop_sepa0 = $calc_lang_0 == "" ? "" : $sc . " ";
                        $dop_sepa1 = $calc_lang_1 == "" ? "" : $sc . " ";
                        $dop_sepa2 = $calc_lang_2 == "" ? "" : $sc . " ";
                        $dop_sepa3 = $calc_lang_3 == "" ? "" : $sc . " ";

//Лучше без пробела ("Цена = 15000" на одной строке может быть "Цена =", на второй "15000"; а если "Цена=15000" всегда выходит на одной строке, т.к. это одно слово)
//                        $left_str0 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_0 . " " : "";
//                        $left_str1 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_1 . " " : "";
//                        $left_str2 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_2 . " " : "";
//                        $left_str3 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_3 . " " : "";
//                        $right_str0 = $link->parent_is_left_calcname == false ? " " . $link->parent_calcname_prefix_lang_0 : "";
//                        $right_str1 = $link->parent_is_left_calcname == false ? " " . $link->parent_calcname_prefix_lang_1 : "";
//                        $right_str2 = $link->parent_is_left_calcname == false ? " " . $link->parent_calcname_prefix_lang_2 : "";
//                        $right_str3 = $link->parent_is_left_calcname == false ? " " . $link->parent_calcname_prefix_lang_3 : "";

                        $left_str0 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_0 . "" : "";
                        $left_str1 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_1 . "" : "";
                        $left_str2 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_2 . "" : "";
                        $left_str3 = $link->parent_is_left_calcname == true ? $link->parent_calcname_prefix_lang_3 . "" : "";
                        $right_str0 = $link->parent_is_left_calcname == false ? "" . $link->parent_calcname_prefix_lang_0 : "";
                        $right_str1 = $link->parent_is_left_calcname == false ? "" . $link->parent_calcname_prefix_lang_1 : "";
                        $right_str2 = $link->parent_is_left_calcname == false ? "" . $link->parent_calcname_prefix_lang_2 : "";
                        $right_str3 = $link->parent_is_left_calcname == false ? "" . $link->parent_calcname_prefix_lang_3 : "";

                        $calc_lang_0 = $calc_lang_0 . ($dop_name_0 == "" ? "" : $dop_sepa0 . $left_str0) . $dop_name_0 . ($dop_name_0 == "" ? "" : $right_str0);
                        $calc_lang_1 = $calc_lang_1 . ($dop_name_1 == "" ? "" : $dop_sepa1 . $left_str1) . $dop_name_1 . ($dop_name_1 == "" ? "" : $right_str1);
                        $calc_lang_2 = $calc_lang_2 . ($dop_name_2 == "" ? "" : $dop_sepa2 . $left_str2) . $dop_name_2 . ($dop_name_2 == "" ? "" : $right_str2);
                        $calc_lang_3 = $calc_lang_3 . ($dop_name_3 == "" ? "" : $dop_sepa3 . $left_str3) . $dop_name_3 . ($dop_name_3 == "" ? "" : $right_str3);
                    }
                }
            }
        }
        $calc_full_lang_0 = $calc_lang_0;
        $calc_full_lang_1 = $calc_lang_1;
        $calc_full_lang_2 = $calc_lang_2;
        $calc_full_lang_3 = $calc_lang_3;

//        // меняем и возвращаем $item
//        // 1000 - макс.размер строковых полей name_lang_x в items
//        $calc_lang_0 = mb_substr($calc_lang_0, 0, 1000);
//        $calc_lang_1 = mb_substr($calc_lang_1, 0, 1000);
//        $calc_lang_2 = mb_substr($calc_lang_2, 0, 1000);
//        $calc_lang_3 = mb_substr($calc_lang_3, 0, 1000);
        // меняем и возвращаем $item
        // 255 - макс.размер строковых полей name_lang_x в items
        $calc_lang_0 = GlobalController::itnm_left($calc_lang_0);
        $calc_lang_1 = GlobalController::itnm_left($calc_lang_1);
        $calc_lang_2 = GlobalController::itnm_left($calc_lang_2);
        $calc_lang_3 = GlobalController::itnm_left($calc_lang_3);
        return ['calc_full_lang_0' => $calc_full_lang_0, 'calc_full_lang_1' => $calc_full_lang_1,
            'calc_full_lang_2' => $calc_full_lang_2, 'calc_full_lang_3' => $calc_full_lang_3,
            'calc_lang_0' => $calc_lang_0, 'calc_lang_1' => $calc_lang_1, 'calc_lang_2' => $calc_lang_2, 'calc_lang_3' => $calc_lang_3];
    }

    function calculate_names(Base $base, Project $project)
    {
        $items = Item::where('base_id', $base->id)->where('project_id', $project->id)->get();
        $rs = false;
        foreach ($items as $item) {
            $rs = $this->calc_value_func($item);
            $item->name_lang_0 = $rs['calc_lang_0'];
            $item->name_lang_1 = $rs['calc_lang_1'];
            $item->name_lang_2 = $rs['calc_lang_2'];
            $item->name_lang_3 = $rs['calc_lang_3'];
            $item->save();
        }
        return redirect()->back();
    }

    function calculate_new_code(Base $base, Project $project)
    {
        $result = 0;
        // Если предложить код при добавлении записи
        if ($base->is_suggest_code == true) {
            //Список, отсортированный по коду
//          $items = Item::where('base_id', $base->id)->orderBy('code')->get();
            $items = Item::all()->where('base_id', $base->id)->where('project_id', $project->id)->sortBy(function ($row) {
                return $row->code;
            })->toArray();
            if ($items == null) {
                $result = 1;
            } else {
                // Предложить код по максимальному значению, иначе - по первому свободному значению
                if ($base->is_suggest_max_code == true) {
                    //$result = strval($items[count($items) - 1]->code) + 1;
                    $result = strval($items[array_key_last($items)]['code']) + 1;
                } else {
                    $i = 0;
                    // Эта строка нужна
                    $result = count($items) + 1;
                    foreach ($items as $key => $item) {
                        $i = $i + 1;
                        if (strval($item['code']) != $i) {
                            $result = $i;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    function recalculation_codes(Base $base, Project $project)
    {
        $items = Item::where('base_id', $base->id)->where('project_id', $project->id)->orderBy('name_lang_0')->get();
        // Чтобы не было ошибки уникальность кода "items:base_id, project_id, code" нарушена
        $i = 0;
        foreach ($items as $item) {
            $i = $i + 1;
            $item->code = -$i;
            $item->save();
        }
        // Непосредственно расчет и присвоение новых кодов
        $i = 0;
        foreach ($items as $item) {
            $i = $i + 1;
            $item->code = $i;
            $item->code_add_zeros();
            $item->save();
        }
        return redirect()->back();
    }

    function verify_number_values()
    {
        // Выбрать только числовые $items
        // В базе данных должно храниться с нулем впереди для правильной сортировки
        $items = Item::whereHas('base', function ($query) {
            $query->where('type_is_number', true);
        })->get();
        foreach ($items as $item) {
            foreach (config('app.locales') as $key => $value) {
                $value = GlobalController::restore_number_from_item($item->base, $item['name_lang_0']);
                $item['name_lang_' . $key] = GlobalController::save_number_to_item($item->base, $value);
            }
            $item->save();
        }
        $result = trans('main.processed') . " " . count($items) . " " . mb_strtolower(trans('main.records')) . ".";
        return view('message', ['message' => $result]);
    }

    function verify_table_texts()
    {
        $result = trans('main.deleted') . ' text.ids: ';
        $i = 0;
        $texts = Text::get();
        foreach ($texts as $text) {
            $item = Item::find($text->item_id);
            $delete = false;
            // Если найдено
            if ($item) {
                if (!$item->base->type_is_text()) {
                    $delete = true;
                }
                // Если не найдено
            } else {
                $delete = true;
            }
            if ($delete) {
                $text->delete();
                if ($i > 0) {
                    $result = $result . ", ";
                }
                $result = $result . $text->id;
                $i = $i + 1;
            }
        }
        $result = $result . " - " . $i . " " . mb_strtolower(trans('main.recs_genitive_case')) . ".";
        return view('message', ['message' => $result]);
    }

    function item_from_base_code(Base $base, Project $project, $code)
    {
        $item_id = 0;
        $item_name = trans('main.no_information') . '!';
        $item = Item::where('project_id', $project->id)->where('base_id', $base->id)->where('code', $code)->get()->first();
        if ($item != null) {
            $item_id = $item->id;
            $item_name = $item->name();
        }
        return ['item_id' => $item_id, 'item_name' => $item_name];
    }

    function filesize_message($fs, $mx)
    {
        return trans('main.size_selected_file') . ' (' . $fs . ' ' . mb_strtolower(trans('main.byte')) . ') '
            . mb_strtolower(trans('main.must_less_equal')) . ' (' . $mx . ' ' . mb_strtolower(trans('main.byte')) . ') !';
    }

    static function links_info(Base $base, Role $role)
    {
        $link_id_array = array();
        $matrix = array(array());
        $links = $base->child_links->sortBy('parent_base_number');

        $k = 0;
        foreach ($links as $link) {
            $base_link_right = GlobalController::base_link_right($link, $role);
            if ($base_link_right['is_list_link_enable'] == true) {
                $is_list_base_calc = $base_link_right['is_list_base_calc'];
                $link_id_array[] = $link->id;
                // 0-ая строка с link->id
                $matrix[0][$k] = ['parent_level_id' => null, 'link_id' => $link->id, 'work_field' => null, 'work_link' => null, 'is_list_base_calc' => $is_list_base_calc, 'fin_link' => null, 'view_field' => null, 'view_name' => '', 'colspan' => 0, 'rowspan' => 0];
                // строки с уровнями
                $matrix[1][$k] = ['parent_level_id' => $link->parent_level_id_0, 'link_id' => $link->id, 'work_field' => null, 'work_link' => null, 'is_list_base_calc' => $is_list_base_calc, 'fin_link' => null, 'view_field' => null, 'view_name' => '', 'colspan' => 0, 'rowspan' => 0];
                $matrix[2][$k] = ['parent_level_id' => $link->parent_level_id_1, 'link_id' => $link->id, 'work_field' => null, 'work_link' => null, 'is_list_base_calc' => $is_list_base_calc, 'fin_link' => null, 'view_field' => null, 'view_name' => '', 'colspan' => 0, 'rowspan' => 0];
                $matrix[3][$k] = ['parent_level_id' => $link->parent_level_id_2, 'link_id' => $link->id, 'work_field' => null, 'work_link' => null, 'is_list_base_calc' => $is_list_base_calc, 'fin_link' => null, 'view_field' => null, 'view_name' => '', 'colspan' => 0, 'rowspan' => 0];
                $matrix[4][$k] = ['parent_level_id' => $link->parent_level_id_3, 'link_id' => $link->id, 'work_field' => null, 'work_link' => null, 'is_list_base_calc' => $is_list_base_calc, 'fin_link' => null, 'view_field' => null, 'view_name' => '', 'colspan' => 0, 'rowspan' => 0];
                $k = $k + 1;
            }
        }

        // 0-ая строка с link->id + 4 строки с уровнями
        $rows = 5;
        $cols = $k;

        $error_message = "";

        // Заполнение $matrix[$i][$j]['work_field'] и $matrix[$i][$j]['work_link']
        // 0-ая строка с link->id
        $i = 0;
        for ($j = 0; $j < $cols; $j++) {
            $matrix[$i][$j]['work_field'] = 'link' . $matrix[$i][$j]['link_id'];
            $matrix[$i][$j]['work_link'] = true;
        }
        // Сколько строк полностью заполнено
        // $rowmax - максимальная строка
        $rowmax = 0;  // "$rowmax = 0;" нужно, как минимум одна 0-ая строка есть
        // "$i = 1" - начинать с 1-ой строки, т.к. 0-ая заполнена link->id
        for ($i = 1; $i < $rows; $i++) {
            $k = 0;
            for ($j = 0; $j < $cols; $j++) {
                if ($matrix[$i][$j]['parent_level_id'] != null) {
                    $matrix[$i][$j]['work_field'] = 'level' . $matrix[$i][$j]['parent_level_id'];
                    $matrix[$i][$j]['work_link'] = false;
                    $k = $k + 1;
                }
            }
            // Есть хотя бы одна заполненная ячейка в строке
            if ($k > 0) {
                for ($j = 0; $j < $cols; $j++) {
                    // Если в links->parent_level_id_x не заполнено, то тогда в эту ячейку выводится link->id (точнее link->parent_label())
                    if ($matrix[$i][$j]['parent_level_id'] == null) {
                        $matrix[$i][$j]['work_field'] = 'link' . $matrix[$i][$j]['link_id'];
                        $matrix[$i][$j]['work_link'] = true;
                    }
                }
                $rowmax = $i;
            }
        }

        // "$i = 1" - начинать с 1-ой строки, т.к. 0-ая заполнена link->id
        for ($i = 1; $i < $rowmax; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                if ($matrix[$i][$j]['work_field'] == null) {
                    $error_message = trans('main.levels_row_is_not_populated_in_settings')
                        . ' (' . mb_strtolower(trans('main.level')) . '_' . ($i - 1) . ')!';
                    break;
                }
            }
        }

        // Если нет ошибки и есть строки для вывода
        if ($error_message == '') {
            // "$rows = $rowmax + 1;" нужно
            $rows = $rowmax + 1;

            // Цикл расчета 'colspan'
            for ($i = 0; $i < $rows; $i++) {
                $k = 0;
                for ($j = 0; $j < $cols; $j++) {
                    if ($matrix[$i][$j]['work_field'] != $matrix[$i][$k]['work_field']) {
                        $k = $j;
                    }
                    $matrix[$i][$k]['colspan'] = $matrix[$i][$k]['colspan'] + 1;
                }
            }

            // Цикл расчета 'rowspan'
            for ($j = 0; $j < $cols; $j++) {
                $k = $rowmax;
                for ($i = $rowmax; $i >= 0; $i--) {
                    // Проверка '$matrix[$i][$j]['colspan'] != $matrix[$k][$j]['colspan']' нужна,
                    // признак того, что выше этой ячейки подниматься не следует
                    // даже при равенстве '$matrix[$i][$j]['parent_level_id'] = $matrix[$k][$j]['parent_level_id']'
                    if ($matrix[$i][$j]['work_field'] != $matrix[$k][$j]['work_field']
                        || $matrix[$i][$j]['colspan'] != $matrix[$k][$j]['colspan']) {
                        $k = $i;
                    }
                    $matrix[$k][$j]['rowspan'] = $matrix[$k][$j]['rowspan'] + 1;
                }
            }

            // Цикл заполнения $matrix[$i][$j]['view_field'] и $matrix[$i][$j]['view_name']
            for ($i = 0; $i < $rows; $i++) {
                for ($j = 0; $j < $cols; $j++) {
                    if ($matrix[$i][$j]['colspan'] != 0 && $matrix[$i][$j]['rowspan'] != 0) {
                        $matrix[$i][$j]['view_field'] = $matrix[$i][$j]['work_field'];
                        if ($matrix[$i][$j]['work_link'] == true) {
                            $link_id = $matrix[$i][$j]['link_id'];
                            $link = Link::findOrFail($link_id);
                            $matrix[$i][$j]['view_name'] = $link->parent_label();
                            // '$matrix[$i][$j]['fin_link']' = true, если есть право показывать ссылку на таблицу с заданным base
                            // Проверка на '$matrix[$i][$j]['fin_link']' используется  в base_index.php
                            $matrix[$i][$j]['fin_link'] = $matrix[$i][$j]['is_list_base_calc'];
                        } else {
                            $level_id = $matrix[$i][$j]['parent_level_id'];
                            $level = Level::findOrFail($level_id);
                            $matrix[$i][$j]['view_name'] = $level->name();
                            // Присвоить '$matrix[$i][$j]['fin_link']' = false, т.к. $matrix[$i][$j]['work_link'] == false
                            // Проверка на '$matrix[$i][$j]['fin_link']' используется  в base_index.php
                            $matrix[$i][$j]['fin_link'] = false;
                        }
                    }
                }
            }

        } else {
            // Нужно, не удалять
            // Для '<th rowspan="{{$rows + 1}}">' в item/base_index.php при выводе в "шапке" столбцов №, кода и наименования
            $rows = 0;
            $cols = 0;
        }

        return ['link_id_array' => $link_id_array, 'matrix' => $matrix,
            'rows' => $rows, 'cols' => $cols, 'error_message' => $error_message];
    }

    static function get_link_refer_main(Base $base, Link $link_refer_start)
    {
        $link = Link::where('parent_is_child_related', true)
            ->where('child_base_id', $base->id)
            ->where('parent_child_related_start_link_id', $link_refer_start->id)
            ->first();
        return $link;
    }


    static function get_items_main(Base $base, Project $project, Role $role, Link $link = null, Item $item = null)
    {
        // Фильтр данных
        $is_filter = false;
        // В списке использовать поля вычисляемой таблицы
        $is_calcuse = false;
        // Результат, no get()
        $items = null;
        $items_filter = null;
        if ($link) {
            if ($item) {
                // Если это фильтрируемое поле (в связка ЕдинИзмерения-Материал - поле Материал является фильтрируемым полем)
                $is_filter = Link::where('parent_is_child_related', true)->where('parent_child_related_start_link_id', $link->id)->exists();
            }
            // 1.0 В списке выбора использовать поле вычисляемой таблицы
            $is_calcuse = $link->parent_is_in_the_selection_list_use_the_calculated_table_field;
        }
        // Права по base_id
        $base_right = GlobalController::base_right($base, $role);
        //dd($is_filter);
        //dd($is_calcuse);
        if (($is_filter) || ($is_calcuse)) {
            //dd($is_calcuse);
            if ($is_filter) {
                $items_filter = self::get_items_filter_main($base, $project, $role, $link, $item);
                $items = $items_filter;
                //dd($items->get());
            }
            if ($is_calcuse) {
                $items = self::get_items_calc_main($base, $project, $role, $link);
                //dd($items->get());
                if ($is_filter) {
                    // Объединение двух запросов $items_filter и $items(вычисляемые)
                    //$items = Item::select(DB::Raw('items.*'))
                    $items = Item::select(DB::Raw('items.*'))
                        ->joinSub($items, 'items_start', function ($join) {
                            $join->on('items.id', '=', 'items_start.id');
                        })
                        ->joinSub($items_filter, 'items_second', function ($join) {
                            $join->on('items.id', '=', 'items_second.id');
                        });
                    //dd($items->get());
                }
            }
        } else {
            $items = self::get_items_list_main($base, $project, $role);
        }

        // Такая же проверка и в GlobalController (function items_right()),
        // в ItemController (function browser(), get_items_for_link(), get_items_ext_edit_for_link())
        if ($base_right['is_list_base_byuser'] == true) {
            $items = $items->where('created_user_id', GlobalController::glo_user_id());
        }

        //dd($items);
        //dd($items->get());
        return $items;
    }

    // Выборка данных без фильтра и вычисляемых
    static function get_items_list_main(Base $base, Project $project, Role $role)
    {
        // Результат, no get()
        $items = Item::select(DB::Raw('items.*'))
            ->where('items.base_id', $base->id)
            ->where('project_id', $project->id);
        return $items;
    }

    // Выборка данных c вычисляемыми
    static function get_items_calc_main(Base $base, Project $project, Role $role, Link $link)
    {
        // Результат, no get()
        $items = null;

        $set = Set::findOrFail($link->parent_selection_calculated_table_set_id);
        $set_link = $set->link_to;
        // Получаем список из вычисляемой таблицы
        $items = Item::select(DB::Raw('items.*'))
            ->join('mains', 'items.id', '=', 'mains.parent_item_id')
            ->where('mains.link_id', '=', $set_link->id);

        //->orderBy('items.' . $name);

        //                             ->where('items.project_id', $project->id)

//                    1.1 В списке выбора использовать дополнительное связанное поле вычисляемой таблицы
        if ($link->parent_is_use_selection_calculated_table_link_id_0 == true) {
            $link_id = $link->parent_selection_calculated_table_link_id_0;
            // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
            // Список 'items.*' формируется из 'mains.parent_item_id'
            // Связь с вычисляемой таблицей - 'joinSub($items, 'items_start', function ($join) {
            //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
            $items = Item::select(DB::Raw('items.*'))
                ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                ->joinSub($items, 'items_start', function ($join) {
                    $join->on('mains.child_item_id', '=', 'items_start.id');
                })
                ->where('mains.link_id', '=', $link_id)
                ->distinct();
            //->orderBy('items.' . $name);

            //                             ->where('items.project_id', $project->id)

//                        1.2 В списке выбора использовать два дополнительных связанных поля вычисляемой таблицы
            if ($link->parent_is_use_selection_calculated_table_link_id_1 == true) {
                $link_id = $link->parent_selection_calculated_table_link_id_1;
                // Получаем данные из обычной таблицы(невычисляемой) + фильтр проверки наличия в вычисляемой таблице
                // Список 'items.*' формируется из 'mains.parent_item_id'
                // Связь с таблицей-результатом предыдущего запроса - 'joinSub($items, 'items_start', function ($join) {
                //                                $join->on('mains.child_item_id', '=', 'items_start.id')'
                $items = Item::select(DB::Raw('items.*'))
                    ->join('mains', 'items.id', '=', 'mains.parent_item_id')
                    ->joinSub($items, 'items_start', function ($join) {
                        $join->on('mains.child_item_id', '=', 'items_start.id');
                    })
                    ->where('mains.link_id', '=', $link_id)
                    ->distinct();
                //->orderBy('items.' . $name);

                //                             ->where('items.project_id', $project->id)
            }
        }

        return $items;
    }

    // Выборка данных с фильтром
    static function get_items_filter_main(Base $base, Project $project, Role $role, Link $link, Item $item)
    {
        // Результат, no get()
        $items = null;

        // Находим $link_find - (из примера) ЕдиницуИзмерения, $link передано в функцию как Материал
        // Если это фильтрируемое поле (например: в связка ЕдинИзмерения-Материал - поле Материал является фильтрируемым полем)
        $link_find = Link::where('parent_is_child_related', true)->where('parent_child_related_start_link_id', $link->id)->first();
        if ($link_find) {
            $link_result = Link::find($link_find->parent_child_related_result_link_id);
            $result_items = null;
            $result_items_name_options = null;
            $cn = 0;
            $error = false;
            $link = null;
            $mains = null;
            $items_parent = null;
            $items_child = null;

            // список links - маршрутов до поиска нужного объекта
            $links = BaseController::get_array_bases_tree_routes($base->id, $link_result->id, false);
            if ($links) {
                $items_parent = array();
                // добавление элемента в конец массива
                array_unshift($items_parent, $item->id);
                $cn = 0;
                $error = false;
                foreach ($links as $link_value) {
                    $cn = $cn + 1;
                    $link = Link::find($link_value);
                    if (!$link) {
                        $error = true;
                        break;
                    }
                    // обнуление массива $items_child
                    $items_child = array();
                    foreach ($items_parent as $item_id) {
                        // $item используется в цикле
                        $mains = Main::select(['child_item_id'])
                            ->where('parent_item_id', $item_id)->where('link_id', $link->id)->get();
                        if (!$mains) {
                            $error = true;
                            break;
                        }
                        foreach ($mains as $main) {
                            // добавление элемента в конец массива
                            array_unshift($items_child, $main->child_item_id);
                        }
                    }
                    $items_parent = $items_child;
                }
            }
            if (!$error) {
                // проверки "цикл прошел по всем элементам до конца";
                if (count($links) == $cn) {
                    $items = $items_child;

//                    $items = Item::whereIn('id', $items_child)
//                        ->orderBy(\DB::raw("FIELD(id, " . implode(',', $items_child) . ")"));
                    $items = Item::whereIn('id', $items_child);

//                    $items = Item::select(DB::Raw('items.*'))
//                        ->joinSub($items_child, 'items_start', function ($join) {
//                            $join->on('items.id', '=', 'items_start');
//                        });
                }
            }
        }
        //dd($items->get());
        return $items;
    }

}
