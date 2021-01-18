<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Item;
use App\Models\Link;
use App\Models\Main;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    protected function rules()
    {
//    https://qna.habr.com/q/342501
//    use Illuminate\Validation\Rule;
//
//        public function rules()
//    {
//        $rules = [
//            'name_eng'=>'required|string',
//            'field1' => [
//                'required',
//                Rule::unique('table_name')->where(function ($query) {
//                    $query->where('field2', $this->get('field2'));
//                })
//            ],
//        ];
//
//        return $rules;
//    }
//        return [
//            'base_id' => 'exists:bases,id|unique_with: items, base_id, name_lang_0',
//            'name_lang_0' => ['required', 'max:255', 'unique_with: items, base_id, name_lang_0']
//        ];
        // exists:table,column
        // поле должно существовать в заданной таблице базе данных.
        return [
            'code' => ['required', 'unique_with: items, base_id, project_id, code'],
            'name_lang_0' => ['max:1000']
        ];
    }

    protected function name_lang_boolean_rules()
    {
        return [
            'base_id' => 'exists:bases,id|unique_with: items, base_id, name_lang_0',
            'name_lang_0' => ['unique_with: items, base_id, name_lang_0']
        ];

    }

    protected function code_rules()
    {
//        return [
//            'name_lang_0' => ['required', 'max:255']
//        ];
        return [
            'code' => ['required', 'unique_with: items, base_id, code']
        ];
    }

    protected function name_lang_rules()
    {
        return [
            'name_lang_0' => ['max:1000']
        ];
    }

    // Две переменные $sort_by_code и $save_by_code нужны,
    // для случая: когда установлен фильтр (неважно по коду или по наименованию):
    // можно нажимать на заголовки "Код"/"Наименование" - количество записей на экране то же оставаться должно,
    // меняется только сортировка
    function browser($base_id, bool $sort_by_code = true, bool $save_by_code = true, $search = "")
    {
        $base = Base::findOrFail($base_id);
        $name = BaseController::field_name();
        $items = null;
        if ($sort_by_code == true) {
            if ($base->is_code_number == true) {
                // Сортировка по коду числовому
                $items = Item::selectRaw("*, code*1  AS code_value")
                    ->where('base_id', $base_id)->where('project_id', GlobalController::glo_project_id())->orderBy('code_value');
            } else {
                // Сортировка по коду строковому
                $items = Item::where('base_id', $base_id)->where('project_id', GlobalController::glo_project_id())->orderByRaw(strval('code'));
            }
        } else {
            // Сортировка по наименованию
            $items = Item::where('base_id', $base_id)->where('project_id', GlobalController::glo_project_id())->orderByRaw(strval($name));
        }
        if ($items != null) {
            if ($search != "") {
                if ($save_by_code == true) {
                    $items = $items->where('code', 'LIKE', '%' . $search . '%');
                } else {
                    $items = $items->where($name, 'LIKE', '%' . $search . '%');
                }
            }
        }
        return view('item/browser', ['base' => $base, 'sort_by_code' => $sort_by_code, 'save_by_code' => $save_by_code,
            'items' => $items->paginate(30), 'search' => $search]);
    }

    function index()
    {
        $items = null;
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    //$items = Item::all()->sortBy('name_lang_0');
                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_0');
                    break;
                case 1:
                    //$items = Item::all()->sortBy(function($row){return $row->name_lang_1 . $row->name_lang_0;});
                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_1')->orderBy('name_lang_0');
                    break;
                case 2:
                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_2')->orderBy('name_lang_0');
                    break;
                case 3:
                    $items = Item::where('project_id', GlobalController::glo_project_id())->orderBy('base_id')->orderBy('name_lang_3')->orderBy('name_lang_0');
                    break;
            }
        }
        session(['links' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
        return view('item/index', ['items' => $items->paginate(60)]);

//        return view('item/index', ['items' => Item::all()->sortBy(function ($item){
//            return $item->base->name_lang_0 . $item->name_lang_0;})]);
    }

    function base_index(Base $base)
    {
        $base_right = GlobalController::base_right($base);

        session(['links' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
        return view('item/base_index', ['base_right' => $base_right, 'base' => $base,
            'items' => GlobalController::items_right($base)['items']->paginate(60)]);

    }

    function item_index(Item $item, Link $par_link = null)
    {
//        $items = null;
//        $index = array_search(session('locale'), session('glo_menu_save'));
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
        return view('item/item_index', ['item' => $item, 'par_link' => $par_link]);

    }

    function store_link_change(Request $request)
    {
        $item = $request['item'];
        $link = $request['link'];
        return redirect()->route('item.item_index', ['item' => $item, 'par_link' => $link]);
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

    // рекурсивная функция
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

    function ext_show(Item $item)
    {
        return view('item/ext_show', ['type_form' => 'show', 'item' => $item, 'array_calc' => $this->get_array_calc_edit($item)['array_calc']]);
    }

    function ext_create(Base $base, $heading = 0, Link $par_link = null, Item $parent_item = null)
        // '$heading = 0' использовать; аналог '$heading = false', в этом случае так /item/ext_create/{base}//
    {
        $arrays = $this->get_array_calc_create($base, $par_link, $parent_item);
        $array_calc = $arrays['array_calc'];
        $array_disabled = $arrays['array_disabled'];
        $code_new = $this->calculate_new_code($base);
        // Похожая строка внизу
        $code_uniqid = uniqid($base->id . '_', true);

        return view('item/ext_edit', ['base' => $base, 'code_new' => $code_new, 'code_uniqid' => $code_uniqid,
            'heading' => $heading,
            'array_calc' => $array_calc,
            'array_disabled' => $array_disabled,
            'par_link' => $par_link, 'parent_item' => $parent_item]);

    }

    function create()
    {
        return view('item/edit', ['bases' => Base::all()]);
    }

    function ext_store(Request $request, Base $base, $heading)
    {
        //https://webformyself.com/kak-v-php-poluchit-znachenie-checkbox/
        //        if($base->type_is_boolean()){
//            $request->validate($this->name_lang_boolean_rules());
//        }else{
        $request->validate($this->rules());
//        }
        // Проверка на обязательность ввода наименования
        if ($base->is_required_lst_num_str == true && $base->is_calcname_lst == false) {
            // Тип - список или строка
            if ($base->type_is_list() || $base->type_is_string()) {
                $name_lang_array = array();
                // значения null в ""
                $name_lang_array[0] = isset($request->name_lang_0) ? $request->name_lang_0 : "";
                $name_lang_array[1] = isset($request->name_lang_1) ? $request->name_lang_1 : "";
                $name_lang_array[2] = isset($request->name_lang_2) ? $request->name_lang_2 : "";
                $name_lang_array[3] = isset($request->name_lang_3) ? $request->name_lang_3 : "";
                $errors = false;
                $i = 0;
                foreach (session('glo_menu_save') as $lang_key => $lang_value) {
                    if (($base->is_one_value_lst_str == true && $lang_key == 0) || ($base->is_one_value_lst_str == false)) {
                        if ($name_lang_array[$i] === '') {
                            $array_mess['name_lang_' . $i] = trans('main.is_required_lst_num_str') . '!';
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
                    $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str') . '!';
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
        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $item = new Item($request->except('_token', '_method'));
        $item->base_id = $base->id;
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
        // далее этот блок
        // похожая формула ниже (в этой же процедуре)
        if ($base->type_is_boolean()) {
            $item->name_lang_0 = isset($request->name_lang_0) ? "1" : "0";
        }

        // затем этот блок (используется "$base")
        if ($base->type_is_number() || $base->type_is_date() || $base->type_is_boolean()) {
            // присваивание полям наименование строкового значение числа/даты
//            foreach (session('glo_menu_save') as $key => $value) {
//                if ($key > 0) {
//                    $item['name_lang_' . $key] = $item->name_lang_0;
//                }
//            }
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }
        $excepts = array('_token', 'base_id', 'project_id', 'code', '_method', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3');
        $string_langs = $this->get_child_links($base);
        // Формируется массив $code_names - названия полей кодов
        // Формируется массив $string_names - названия полей наименование
        $code_names = array();
        $string_names = array();
        $i = 0;

        foreach ($string_langs as $key => $link) {
            if ($link->parent_base->type_is_string()) {
                $i = 0;
                foreach (session('glo_menu_save') as $lang_key => $lang_value) {
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

        // обработка для логических полей
        // если при вводе формы пометка checkbox не установлена, в $request записи про элемент checkbox вообще нет
        // если при вводе формы пометка checkbox установлена, в $request есть запись со значеним "on"
        // см. https://webformyself.com/kak-v-php-poluchit-znachenie-checkbox/
        foreach ($string_langs as $link) {
            // Проверка нужна
            $base_link_right = GlobalController::base_link_right($link);
            if ($base_link_right['is_edit_link_enable'] == false) {
                continue;
            }
            // похожая формула выше (в этой же процедуре)
            if ($link->parent_base->type_is_boolean()) {
                // у этой команды два предназначения:
                // 1) заменить "on" на "1" при отмеченном checkbox
                // 2) создать новый ([$link->id]-й) элемент массива со значением "0" при выключенном checkbox
                // в базе данных информация хранится как "0" или "1"
                $inputs[$link->id] = isset($inputs[$link->id]) ? "1" : "0";
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

        $array_mess = array();
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
                if ($work_base->is_required_lst_num_str == true) {
                    $control_required = true;
                }
                // это правильно

                //$control_required = true;

            } // Тип - число
            elseif ($work_base->type_is_number()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str == true) {
                    $control_required = true;
                }
            } // Тип - строка
            elseif ($work_base->type_is_string()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str == true) {
                    $control_required = true;
                }
            } // Тип - дата
            elseif ($work_base->type_is_date()) {
                $control_required = true;
            }

            // при типе корректировки поля "строка", "логический" проверять на обязательность заполнения не нужно
            if ($control_required == true) {
                // Тип - строка
                if ($work_base->type_is_string()) {
                    // поиск в таблице items значение с таким же названием и base_id
                    $name_lang_value = null;
                    $name_lang_key = null;
                    $i = 0;
                    foreach (session('glo_menu_save') as $lang_key => $lang_value) {
                        if (($work_base->is_one_value_lst_str == true && $lang_key == 0) || ($work_base->is_one_value_lst_str == false)) {
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
        }

        if ($errors) {
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        // Одно значение у всех языков
        if ($base->is_one_value_lst_str == true) {
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }

        $item->project_id = GlobalController::glo_project_id();
        $item->updated_user_id = Auth::user()->id;

        try {
            // начало транзакции
            DB::transaction(function ($r) use ($item, $keys, $values, $strings_inputs) {

                // Эта команда "$item->save();" нужна, чтобы при сохранении записи стало известно значение $item->id.
                // оно нужно в функции save_main() (для команды "$main->child_item_id = $item->id;");
                $item->save();

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

                // Присвоение данных
                // "$i = 0" использовать, т.к. индексы в массивах начинаются с 0
                $i = 0;
                foreach ($keys as $key) {
                    $main = Main::where('child_item_id', $item->id)->where('link_id', $key)->first();
                    if ($main == null) {
                        $main = new Main();
                    }
                    $this->save_main($main, $item, $keys, $values, $i, $strings_inputs);
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
                $item->save();

            }, 3);  // Повторить три раза, прежде чем признать неудачу
            // окончание транзакции

        } catch (Exception $exc) {
            return trans('transaction_not_completed') . ": " . $exc->getMessage();
        }

        return $heading ? redirect()->route('item.item_index', $item) : redirect(session('links'));

//      return redirect()->back()->withInput();                 # Редиректим его <s>взад</s> на ту же страницу
    }

    private function save_main(Main $main, $item, $keys, $values, $index, $strings_inputs)
    {
        $main->link_id = $keys[$index];
        $main->child_item_id = $item->id;
        // поиск должен быть удачным, иначе "$main->link_id = $keys[$index]" может дать ошибку
        $link = Link::findOrFail($keys[$index]);

        // тип корректировки поля - список
        if ($link->parent_base->type_is_list()) {
            if ($values[$index] == 0) {
                return;
            }
            $main->parent_item_id = $values[$index];

            // тип корректировки поля - строка
        } elseif ($link->parent_base->type_is_string()) {
            // поиск в таблице items значение с таким же названием и base_id
            $item_find = Item::where('base_id', $link->parent_base_id)->where('project_id', GlobalController::glo_project_id())->where('name_lang_0', $values[$index]);
            if ($link->parent_base->is_one_value_lst_str == false) {
                $i = 0;
                foreach (session('glo_menu_save') as $lang_key => $lang_value) {
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
                $item_find->code = uniqid($item_find->id . '_', true);
                // присваивание полям наименование строкового значение числа
                $i = 0;
                foreach (session('glo_menu_save') as $lang_key => $lang_value) {
                    if ($i == 0) {
                        $item_find['name_lang_' . $lang_key] = $values[$index];
                    } else {
                        if ($link->parent_base->is_one_value_lst_str == true) {
                            // Одно значение для наименований у всех языков
                            $item_find['name_lang_' . $lang_key] = $values[$index];
                        } else {
                            $item_find['name_lang_' . $lang_key] = $strings_inputs[$link->id . '_' . $lang_key];
                        }
                    }
                    $i = $i + 1;
                }
                $item_find->project_id = GlobalController::glo_project_id();
                $item_find->updated_user_id = Auth::user()->id;
                $item_find->save();
            }
            $main->parent_item_id = $item_find->id;

            // тип корректировки поля - не строка и не список
        } else {
            // поиск в таблице items значение с таким же названием и base_id
            $item_find = Item::where('base_id', $link->parent_base_id)->where('project_id', GlobalController::glo_project_id())->where('name_lang_0', $values[$index])->first();
            // если не найдено
            if (!$item_find) {
                // создание новой записи в items
                $item_find = new Item();
                $item_find->base_id = $link->parent_base_id;
                // Похожие строки вверху
                $item_find->code = uniqid($item_find->id . '_', true);
                // присваивание полям наименование строкового значение числа
                foreach (session('glo_menu_save') as $key => $value) {
                    $item_find['name_lang_' . $key] = $values[$index];
                }
                $item_find->project_id = GlobalController::glo_project_id();
                $item_find->updated_user_id = Auth::user()->id;
                $item_find->save();
            }
            $main->parent_item_id = $item_find->id;
        }
        $main->updated_user_id = Auth::user()->id;
        $main->save();
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

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
        if (!(($item->base_id == $request->base_id) and ($item->name_lang_0 == $request->name_lang_0))) {
            $request->validate($this->rules());
        }

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

    function ext_update(Request $request, Item $item)
    {
        // Если данные изменились - выполнить проверку. оператор '??' нужны
        if (!($item->name_lang_0 ?? '' == $request->name_lang_0 ?? '')) {
            $request->validate($this->name_lang_rules());
        }
        if (!($item->code == $request->code)) {
            $request->validate($this->code_rules());
        }
        // Проверка на обязательность ввода
        if ($item->base->is_required_lst_num_str == true && $item->base->is_calcname_lst == false) {
            // Тип - список или строка
            if ($item->base->type_is_list() || $item->base->type_is_string()) {
                $name_lang_array = array();
                // значения null в ""
                $name_lang_array[0] = isset($request->name_lang_0) ? $request->name_lang_0 : "";
                $name_lang_array[1] = isset($request->name_lang_1) ? $request->name_lang_1 : "";
                $name_lang_array[2] = isset($request->name_lang_2) ? $request->name_lang_2 : "";
                $name_lang_array[3] = isset($request->name_lang_3) ? $request->name_lang_3 : "";
                $errors = false;
                $i = 0;
                foreach (session('glo_menu_save') as $lang_key => $lang_value) {
                    if (($item->base->is_one_value_lst_str == true && $lang_key == 0) || ($item->base->is_one_value_lst_str == false)) {
                        if ($name_lang_array[$i] === '') {
                            $array_mess['name_lang_' . $i] = trans('main.is_required_lst_num_str') . '!';
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
                    $array_mess['name_lang_0'] = trans('main.is_required_lst_num_str') . '!';
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

        $data = $request->except('_token', '_method');
        $item->fill($data);
        $item->project_id = GlobalController::glo_project_id();
        $item->updated_user_id = Auth::user()->id;
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
        $item->name_lang_0 = isset($request->name_lang_0) ? $request->name_lang_0 : "";
        $item->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $item->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $item->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        // далее этот блок
        // похожая формула ниже (в этой же процедуре)
        if ($item->base->type_is_boolean()) {
            $item->name_lang_0 = isset($request->name_lang_0) ? "1" : "0";
        }

        // затем этот блок (используется "$item->base")
        if ($item->base->type_is_number() || $item->base->type_is_date() || $item->base->type_is_boolean()) {
            // присваивание полям наименование строкового значение числа/даты
//            foreach (session('glo_menu_save') as $key => $value) {
//                if ($key > 0) {
//                    $item['name_lang_' . $key] = $item->name_lang_0;
//                }
//            }
            $item->name_lang_1 = $item->name_lang_0;
            $item->name_lang_2 = $item->name_lang_0;
            $item->name_lang_3 = $item->name_lang_0;
        }

        $excepts = array('_token', 'base_id', 'project_id', 'code', '_method', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3');
        $string_langs = $this->get_child_links($item->base);

        // Формируется массив $code_names - названия полей кодов
        // Формируется массив $string_names - названия полей наименование
        $code_names = array();
        $string_names = array();
        $i = 0;
        foreach ($string_langs as $key => $link) {
            if ($link->parent_base->type_is_string()) {
                $i = 0;
                foreach (session('glo_menu_save') as $lang_key => $lang_value) {
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
        // обработка для логических полей
        // если при вводе формы пометка checkbox не установлена, в $request записи про элемент checkbox вообще нет
        // если при вводе формы пометка checkbox установлена, в $request есть запись со значеним "on"
        // см. https://webformyself.com/kak-v-php-poluchit-znachenie-checkbox/
        foreach ($string_langs as $link) {
            // Проверка нужна
            $base_link_right = GlobalController::base_link_right($link);
            if ($base_link_right['is_edit_link_enable'] == false) {
                continue;
            }
            // похожая формула выше (в этой же процедуре)
            if ($link->parent_base->type_is_boolean()) {
                // у этой команды два предназначения:
                // 1) заменить "on" на "1" при отмеченном checkbox
                // 2) создать новый ([$link->id]-й) элемент массива со значением "0" при выключенном checkbox
                // в базе данных информация хранится как "0" или "1"
                $inputs[$link->id] = isset($inputs[$link->id]) ? "1" : "0";
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

        $array_mess = array();
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
                if ($work_base->is_required_lst_num_str == true) {
                    $control_required = true;
                }
                // это правильно
                //$control_required = true;
            } // Тип - число
            elseif ($work_base->type_is_number()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str == true) {
                    $control_required = true;
                }
            } // Тип - строка
            elseif ($work_base->type_is_string()) {
                // Проверка на обязательность ввода
                if ($work_base->is_required_lst_num_str == true) {
                    $control_required = true;
                }
            } // Тип - дата
            elseif ($work_base->type_is_date()) {
                $control_required = true;
            }

            // при типе корректировки поля "строка", "логический" проверять на обязательность заполнения не нужно
            if ($control_required == true) {
                // Тип - строка
                if ($work_base->type_is_string()) {
                    // поиск в таблице items значение с таким же названием и base_id
                    $name_lang_value = null;
                    $name_lang_key = null;
                    $i = 0;
                    foreach (session('glo_menu_save') as $lang_key => $lang_value) {
                        if (($work_base->is_one_value_lst_str == true && $lang_key == 0) || ($work_base->is_one_value_lst_str == false)) {
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
        }

        if ($errors) {
            // повторный вызов формы
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        // Одно значение у всех языков
        if ($item->base->is_one_value_lst_str == true) {
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
            DB::transaction(function ($r) use ($item, $keys, $values, $strings_inputs) {

                //$item->save();

                // после ввода данных в форме массив состоит:
                // индекс массива = link_id (для занесения в links->id)
                // значение массива = item_id (для занесения в mains->parent_item_id)
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
                foreach ($keys as $key) {
                    $main = Main::where('child_item_id', $item->id)->where('link_id', $key)->first();
                    if ($main == null) {
                        $main = new Main();
                    }
                    $this->save_main($main, $item, $keys, $values, $i, $strings_inputs);
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
                $item->save();

            }, 3);  // Повторить три раза, прежде чем признать неудачу
            // окончание транзакции

        } catch (Exception $exc) {
            return trans('transaction_not_completed') . ": " . $exc->getMessage();
        }

        // удаление неиспользуемых данных
        $this->delete_items_old($array_calc);

        //return redirect()->route('item.base_index', $item->base->id);
        return redirect(session('links'));
    }

    private function delete_items_old($array_calc)
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

    function ext_edit(Item $item, Link $par_link = null, Item $parent_item = null)
    {
        $arrays = $this->get_array_calc_edit($item, $par_link, $parent_item);
        $array_calc = $arrays['array_calc'];
        $array_disabled = $arrays['array_disabled'];
        if ($item->code == "" && $item->base->is_code_needed == false) {
            // Похожая строка есть и в ext_create
            $item->code = uniqid($item->base_id . '_', true);
        }

        return view('item/ext_edit', ['base' => $item->base, 'item' => $item,
            'array_calc' => $array_calc,
            'array_disabled' => $array_disabled,
            'par_link' => $par_link, 'parent_item' => $parent_item]);
    }

    function ext_delete_question(Item $item, $heading = false)
    {
        return view('item/ext_show', ['type_form' => 'delete_question', 'item' => $item,
            'array_calc' => $this->get_array_calc_edit($item)['array_calc'], 'heading' => $heading]);
    }

    function ext_delete(Item $item, $heading = false)
    {
        $item->delete();
        return $heading == true ? redirect()->route('item.base_index', $item->base_id) : redirect(session('links'));
    }

    static function get_items_for_link(Link $link)
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
                $index = array_search(session('locale'), session('glo_menu_save'));
                if ($index !== false) {   // '!==' использовать, '!=' не использовать
                    $name = 'name_lang_' . $index;
                }

                // список items по выбранному child_base_id
                $result_child_base_items = Item::select(['id', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'])->where('base_id', $link->child_base_id)->where('project_id', GlobalController::glo_project_id())->orderBy($name)->get();
                foreach ($result_child_base_items as $item) {
                    $result_child_base_items_options = $result_child_base_items_options . "<option value='" . $item->id . "'>" . $item->name() . "</option>";
                }

                // список items по выбранному parent_base_id
                $result_parent_base_items = Item::select(['id', 'base_id', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'])->where('base_id', $link->parent_base_id)->where('project_id', GlobalController::glo_project_id())->orderBy($name)->get();
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
            // возвращает маршрут $link_ids по вычисляемым полям до первого найденного постоянного link_id ($const_link_id_start)
            $rs = LinkController::get_link_ids_from_calc_link($link_result);
            $const_link_id_start = $rs['const_link_id_start'];
            $link_ids = $rs['link_ids'];
            // вычисляем первоначальный $item;
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
                        $result_item_name = $item->name();
                        $result_item_name_options = "<option value='" . $item->id . "'>" . $item->name() . "</option>";
                    }
                }
            }
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
            $result = '<ul type="circle"><li>'
                . $item->base->name() . ' (' . $item->base->name() . ': ' . ' <b>' . $item->name() . '</b>)' . $result . '</li></ul>';

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
            $str = self::form_tree_start($items, $main->parent_item_id, $level);
            $result = $result . '<li>' . $main->link->id . ' ' . $main->link->parent_label() . ' (' . $main->link->parent_base->name() . ': ' . '<b>' . $main->parent_item->name() . '</b>)'
                . $str . '</li>';
        }
        $result = $result . "</ul>";
        return $result;
    }

    // Функция calc_value_func() вычисляет наимеования для записи $item
    private
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
                // если поле входит в состав вычисляемого составного поля
                if ($link->parent_is_calcname == true) {
                    // $first_run = false запускается только для однородных значений (например: ФизЛицо имеет поле Мать(ФизЛицо), Отец(ФизЛицо))
                    if (($first_run == true) ||
                        (($first_run == false)
                            && (($item->base->is_same_small_calcname == false)
                                || ($item->base->is_same_small_calcname == true) && ($link->parent_is_small_calcname == true)))) {
                        if ($value == null) {
                            // Проверка на вычисляемые поля
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
                                            $result_func = $this->get_parent_item_from_calc_child_item($item_find, $link, false);
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
                        // символ разделения для вычисляемых полей
                        $sc = trim($item->base->sepa_calcname);
                        $dop_sepa0 = $calc_lang_0 == "" ? "" : $sc . " ";
                        $dop_sepa1 = $calc_lang_1 == "" ? "" : $sc . " ";
                        $dop_sepa2 = $calc_lang_2 == "" ? "" : $sc . " ";
                        $dop_sepa3 = $calc_lang_3 == "" ? "" : $sc . " ";
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
        // меняем и возвращаем $item
        // 1000 - макс.размер строковых полей name_lang_x в items
        $calc_lang_0 = mb_substr($calc_lang_0, 0, 1000);
        $calc_lang_1 = mb_substr($calc_lang_1, 0, 1000);
        $calc_lang_2 = mb_substr($calc_lang_2, 0, 1000);
        $calc_lang_3 = mb_substr($calc_lang_3, 0, 1000);
        return ['calc_lang_0' => $calc_lang_0, 'calc_lang_1' => $calc_lang_1, 'calc_lang_2' => $calc_lang_2, 'calc_lang_3' => $calc_lang_3];
    }

    function calculate_name(Base $base)
    {
        $items = Item::where('base_id', $base->id)->where('project_id', GlobalController::glo_project_id())->get();
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

    function calculate_new_code(Base $base)
    {
        $result = 0;
        // Если предложить код при добавлении записи
        if ($base->is_suggest_code == true) {
            //Список, отсортированный по коду
//          $items = Item::where('base_id', $base->id)->orderBy('code')->get();
            $items = Item::all()->where('base_id', $base->id)->where('project_id', GlobalController::glo_project_id())->sortBy(function ($row) {
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


    function recalculation_codes(Base $base)
    {
        $items = Item::where('base_id', $base->id)->where('project_id', GlobalController::glo_project_id())->orderBy('name_lang_0')->get();
        $i = 0;
        foreach ($items as $item) {
            $i = $i + 1;
            $item->code = $i;
            $item->code_add_zeros();
            $item->save();
        }
        return redirect()->back();
    }

    function item_from_base_code(Base $base, $code)
    {
        $item_id = 0;
        $item_name = trans('main.no_information') . '!';
        $item = Item::where('project_id', GlobalController::glo_project_id())->where('base_id', $base->id)->where('code', $code)->get()->first();
        if ($item != null) {
            $item_id = $item->id;
            $item_name = $item->name();
        }
        return ['item_id' => $item_id, 'item_name' => $item_name];
    }

}
