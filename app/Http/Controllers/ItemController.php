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
    // Использовать знак вопроса "/{project_id?}" (web.php)
    //              равенство null "$project_id = null" (ItemController.php),
    // иначе ошибка в function seach_click() - open('{{route('item.browser', '')}}' ...
    function browser($base_id, $project_id = null, $role_id = null, bool $sort_by_code = true, bool $save_by_code = true, $search = "")
    {
        $base = Base::findOrFail($base_id);
        $project = Project::findOrFail($project_id);
        $role = Role::findOrFail($role_id);
        $base_right = GlobalController::base_right($base, $role);
        $name = BaseController::field_name();
        $items = null;
        if ($sort_by_code == true) {
            if ($base->is_code_number == true) {
                // Сортировка по коду числовому
                $items = Item::selectRaw("*, code*1  AS code_value")
                    ->where('base_id', $base_id)->where('project_id', $project->id)->orderBy('code_value');
            } else {
                // Сортировка по коду строковому
                $items = Item::where('base_id', $base_id)->where('project_id', $project->id)->orderByRaw(strval('code'));
            }
        } else {
            // Сортировка по наименованию
            $items = Item::where('base_id', $base_id)->where('project_id', $project->id)->orderByRaw(strval($name));
        }
        if ($items != null) {
            // Такая же проверка и в GlobalController (function items_right()),
            // в ItemController (function browser(), get_items_for_link())
            if ($base_right['is_list_base_byuser'] == true) {
                $items = $items->where('created_user_id', GlobalController::glo_user_id());
            }
            if ($search != "") {
                if ($save_by_code == true) {
                    $items = $items->where('code', 'LIKE', '%' . $search . '%');
                } else {
                    $items = $items->where($name, 'LIKE', '%' . $search . '%');
                }
            }
        }
        return view('item/browser', ['base' => $base, 'project' => $project, 'role' => $role, 'base_right' => $base_right, 'sort_by_code' => $sort_by_code, 'save_by_code' => $save_by_code,
            'items' => $items->paginate(30), 'search' => $search]);
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

        $base_right = GlobalController::base_right($base, $role);
        $items_right = GlobalController::items_right($base, $project, $role);
        $items = $items_right['items'];

        if ($items) {
            session(['base_index_previous_url' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
            return view('item/base_index', ['base_right' => $base_right, 'base' => $base, 'project' => $project, 'role' => $role,
                'items' => $items->paginate(60)]);
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
                    }
                    if ($value === '0') {
                        $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                        $errors = true;
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

                // Эта команда "$item->save();" нужна, чтобы при сохранении записи стало известно значение $item->id.
                // оно нужно в функции save_main() (для команды "$main->child_item_id = $item->id;");
                $item->save();
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
                Mail::send(['html' => 'mail/item_create'], ['item' => $item],
                    function ($message) use ($email_to, $appname, $item) {
                        $message->to($email_to, '')->subject(trans('main.new_record') . ' - ' . $item->base->name());
                        $message->from(env('MAIL_FROM_ADDRESS', ''), $appname);
                    });
            }
        }

//return $heading ? redirect()->route('item.item_index', $item) : redirect(session('links'));
        return $heading ? redirect()->route('item.item_index', $item) : redirect()->route('item.base_index', ['base' => $base, 'project' => $project, 'role' => $role]);
//return redirect()->route('item.base_index', ['base'=>$item->base, 'project'=>$item->project, 'role'=>$role]);

    }

    private
    function save_reverse_sets(Item $item)
    {
        $itpv = Item::findOrFail($item->id);
        $mains = $itpv->child_mains()->get();
        $inputs_reverse = array();
        $invals = array();
        foreach ($mains as $key => $main) {
            $inputs_reverse[$main->link_id] = $main->parent_item_id;
        }

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
        $this->save_sets($itpv, $keys_reverse, $values_reverse, $valits_reverse, true);
    }

    private
    function is_save_sets(Item $item)
    {
        $set_main = Set::select(DB::Raw('sets.*, lt.child_base_id as to_child_base_id, lt.parent_base_id as to_parent_base_id'))
            ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
            ->where('lf.child_base_id', '=', $item->base_id)
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
            ->orderBy('sets.link_from_id')
            ->orderBy('sets.link_to_id')->get();

        $set_group_by_base_to = $set_main->groupBy('to_child_base_id')->sortBy('to_child_base_id');

        //$table2 = Set::select(DB::Raw('$table1.*'))->get();
        foreach ($set_group_by_base_to as $to_key => $to_value) {
            $items = Item::where('base_id', $to_key)->where('project_id', $item->project_id);
//            $items = $items->whereHas('child_mains', function ($query) {
//                $query->where('link_id', 41)->where('parent_item_id', 388);
//            });
            $set_base_to = $set_main->where('to_child_base_id', '=', $to_key)->sortBy('to_parent_base_id');
            $set_is_group = $set_base_to->where('is_group', true);
            //$base_to_id = $key;
            $error = true;
            $found = false;
            $item_seek = null;
            // Поиск $item_seek
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

            if (!$error) {
                if (!$found) {
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
                //$items = $items->get();
                $error = true;
                $found = false;
                $valnull = false;
                // Фильтры
                foreach ($set_base_to as $key => $value) {
                    $nk = 0;
                    foreach ($keys as $k => $v) {
                        if ($v == $value['link_from_id']) {
                            $nk = $k;
                            break;
                        }
                    }
                    if ($nk != 0) {
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
                        $seek_item = false;
                        $seek_value = 0;


                        if ($value->link_to->parent_base->type_is_number() && is_numeric($values[$nk])) {
                            $ch = $values[$nk];
                        } else {
                            $ch = 0;
                        }
                        if ($value->is_group == true) {
                            $main->parent_item_id = $valits[$nk];
                        } elseif ($value->is_update == true) {
                            if ($value->is_upd_plus == true) {
                                $seek_item = true;

                                $seek_value = $vl + $kf * $ch;
                                if ($seek_value == 0) {
                                    $valnull = true;
                                }
                            } elseif ($value->is_upd_minus == true) {
                                $seek_item = true;
                                $seek_value = $vl - $kf * $ch;
                                if ($seek_value == 0) {
                                    $valnull = true;
                                }
                            } elseif ($value->is_upd_replace == true) {
                                $main->parent_item_id = $valits[$nk];
                            }
                        }

                        if ($seek_item == true) {
                            $item_find = Item::where('base_id', $value->link_to->parent_base_id)->where('project_id', $item->project_id)
                                ->where('name_lang_0', $seek_value)->first();
                            // если не найдено
                            if (!$item_find) {
                                // создание новой записи в items
                                $item_find = new Item();
                                $item_find->base_id = $value->link_to->parent_base_id;
                                // Похожие строки вверху
                                $item_find->code = uniqid($item_find->base_id . '_', true);
                                // присваивание полям наименование строкового значение числа
                                foreach (config('app.locales') as $key => $value) {
                                    $item_find['name_lang_' . $key] = $seek_value;
                                }
                                $item_find->project_id = $item->project_id;
                                // при создании записи "$item->created_user_id" заполняется
                                $item_find->created_user_id = Auth::user()->id;
                                $item_find->updated_user_id = Auth::user()->id;
                                $item_find->save();
                            }
                            $main->parent_item_id = $item_find->id;
                        }
                        $main->save();

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

                // Если остаток нулевой
                if ($valnull) {
                    $item_seek->delete();
                }

            }
        }

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
                        Mail::send(['html' => 'mail/login_site'], ['remote_addr' => $_SERVER['REMOTE_ADDR'],
                            'http_user_agent' => $_SERVER['HTTP_USER_AGENT'], 'appname' => $appname],
                            function ($message) use ($appname) {
                                $message->to(env('MAIL_TO_ADDRESS_MODERATION', 'moderation@rsb0807.kz'), '')->subject("Модерация '" . $appname . "'");
                                $message->from(env('MAIL_FROM_ADDRESS', 'support@rsb0807.kz'), $appname);
                            });
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
            if (($link->parent_base->type_is_number()) && ($link->parent_base->is_required_lst_num_str_txt_img_doc == false)) {
                if ($values[$index] == 0) {
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
                            Mail::send(['html' => 'mail/login_site'], ['remote_addr' => $_SERVER['REMOTE_ADDR'],
                                'http_user_agent' => $_SERVER['HTTP_USER_AGENT'], 'appname' => $appname],
                                function ($message) use ($appname) {
                                    $message->to(env('MAIL_TO_ADDRESS_MODERATION', 'moderation@rsb0807.kz'), '')->subject("Модерация '" . $appname . "'");
                                    $message->from(env('MAIL_FROM_ADDRESS', 'support@rsb0807.kz'), $appname);
                                });
                        }

                    } else {
                        // Без модерации
                        $item->name_lang_1 = "0";
                    }
                } else {
                    $item->name_lang_1 = "";
                }
                $item->name_lang_2 = "";
                $item->name_lang_3 = "";
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
                }
                if ($errors) {
                    // повторный вызов формы
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($array_mess);
                }
                // Тип - изображение
            } elseif ($item->base->type_is_image()) {
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
            } elseif ($item->base->type_is_document()) {
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
                    }
                    if ($value === '0') {
                        $array_mess[$key] = trans('main.no_data_on') . ' "' . $link->parent_base->name() . '"!';
                        $errors = true;
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
            DB::transaction(function ($r) use ($item, $it_texts, $keys, $values, $strings_inputs) {

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

                $this->save_reverse_sets($item);

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
                Mail::send(['html' => 'mail/item_update'], ['item' => $item],
                    function ($message) use ($email_to, $appname, $item) {
                        $message->to($email_to, '')->subject(trans('main.edit_record') . ' - ' . $item->base->name());
                        $message->from(env('MAIL_FROM_ADDRESS', ''), $appname);
                    });
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

                        $this->save_reverse_sets($item);

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
                    Mail::send(['html' => 'mail/item_delete'], ['item' => $item, 'deleted_user_date_time' => $deleted_user_date_time],
                        function ($message) use ($email_to, $appname, $item) {
                            $message->to($email_to, '')->subject(trans('main.delete_record') . ' - ' . $item->base->name());
                            $message->from(env('MAIL_FROM_ADDRESS', ''), $appname);
                        });
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

    static function get_items_for_link(Link $link, Project $project, Role $role)
    {
        $result_parent_label = '';
        $result_child_base_name = '';
        $result_parent_base_name = '';
        $result_child_base_items = [];
        $result_parent_base_items = [];
        $result_child_base_items_options = '';
        $result_parent_base_items_options = '';
        if ($link != null) {
            // наименование
            $result_parent_label = $link->parent_label();
            // наименование child_base и parent_base
            $result_child_base_name = $link->child_base->name();
            $result_parent_base_name = $link->parent_base->name();
            // если это фильтрируемое поле - то, тогда загружать весь список не нужно
            $link_exists = Link::where('parent_is_child_related', true)->where('parent_child_related_start_link_id', $link->id)->exists();
            if ($link_exists == null) {

                $name = "";  // нужно, не удалять
                $index = array_search(App::getLocale(), config('app.locales'));
                if ($index !== false) {   // '!==' использовать, '!=' не использовать
                    $name = 'name_lang_' . $index;
                }

                // список items по выбранному child_base_id
                $result_child_base_items = Item::select(['id', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'])->where('base_id', $link->child_base_id)->where('project_id', $project->id)->orderBy($name)->get();
                foreach ($result_child_base_items as $item) {
                    $result_child_base_items_options = $result_child_base_items_options . "<option value='" . $item->id . "'>" . $item->name() . "</option>";
                }

                // список items по выбранному parent_base_id
                $base_right = GlobalController::base_right($link->parent_base, $role);
                $result_parent_base_items = Item::select(['id', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3', 'created_user_id'])->where('base_id', $link->parent_base_id)->where('project_id', $project->id)->orderBy($name);
                // Такая же проверка и в GlobalController (function items_right()),
                // в ItemController (function browser(), get_items_for_link())
                if ($base_right['is_list_base_byuser'] == true) {
                    $result_parent_base_items = $result_parent_base_items->where('created_user_id', GlobalController::glo_user_id());
                }
                $result_parent_base_items = $result_parent_base_items->get();
                foreach ($result_parent_base_items as $item) {
                    $result_parent_base_items_options = $result_parent_base_items_options . "<option value='" . $item->id . "'>" . $item->name() . "</option>";
                }
            }
        }
        return [
            'result_parent_label' => $result_parent_label,
            'result_child_base_name' => $result_child_base_name,
            'result_parent_base_name' => $result_parent_base_name,
            'result_child_base_items' => $result_child_base_items,
            'result_parent_base_items' => $result_parent_base_items,
            'result_child_base_items_options' => $result_child_base_items_options,
            'result_parent_base_items_options' => $result_parent_base_items_options,
        ];
    }

    static function get_child_items_from_parent_item(Base $base_start, Item $item_start, Link $link_result)
    {
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

    static function form_tree($item_id)
    {
        $item = Item::find($item_id);
        $items = array();
        $result = self::form_tree_start($items, $item_id, 0);
        if ($result != '') {
//            $result = '<ul type="circle"><li>'
//                . $item->base->name() . ' (' . $item->base->name() . ': ' . ' <b>' . $item->name() . '</b>)' . $result . '</li></ul>';
            $result = '<ul type="circle"><li>' . $item->base->name() . ': ' . ' <b>' . $item->name() . '</b>' . $result . '</li></ul>';
        }
        return $result;
    }

// $items нужно - чтобы не было бесконечного цикла
//static function form_tree_start($items, $id, $level)   - можно использовать так
//static function form_tree_start(&$items, $id, $level)  - и так - результаты разные
    static function form_tree_start(&$items, $id, $level)
    {
        $level = $level + 1;
        $result = '<ul type="circle">';
//        $mains = Main::all()->where('child_item_id', $id)->sortBy(function ($row) {
//            return $row->parent_item->name();
//        });
        $mains = Main::all()->where('child_item_id', $id)->sortBy(function ($row) {
            return $row->link->parent_base_number;
        });
        if (count($mains) == 0) {
            return '';
        }
        if (!(array_search($id, $items) === false)) {
            return '';
        }
        $items[count($items)] = $id;
        foreach ($mains as $main) {
            $str = '';
//            $result = $result . '<li>' . $main->link->id . ' ' . $main->link->parent_label() . ' (' . $main->link->parent_base->name() . ': ' . '<b>' . $main->parent_item->name() . '</b>)'
//                . $str . '</li>';
            $str = self::form_tree_start($items, $main->parent_item_id, $level);
            $result = $result . '<li>' . $main->link->parent_base->name() . ': ' . '<b>' . $main->parent_item->name() . '</b>' . $str . '</li>';
        }
        $result = $result . "</ul>";
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

    function calculate_name(Base $base, Project $project)
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
        //return redirect()->back();
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

}
