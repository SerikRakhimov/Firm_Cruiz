<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{


    protected function rules()
    {
        // sun
//        return [
//            'name_lang_0' => ['required', 'max:255', 'unique_with: bases, name_lang_0'],
//            'names_lang_0' => ['required', 'max:255', 'unique_with: bases, names_lang_0'],
//        ];
        return [
            'name_lang_0' => ['required', 'max:255'],
            'names_lang_0' => ['required', 'max:255'],
        ];
    }

    function index()
    {
        $bases = null;
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    //$bases = Base::all()->sortBy('name_lang_0');
                    $bases = Base::orderBy('name_lang_0');
                    break;
                case 1:
                    //$bases = Base::all()->sortBy(function($row){return $row->name_lang_1 . $row->name_lang_0;});
                    $bases = Base::orderBy('name_lang_1')->orderBy('name_lang_0');
                    break;
                case 2:
                    $bases = Base::orderBy('name_lang_2')->orderBy('name_lang_0');
                    break;
                case 3:
                    $bases = Base::orderBy('name_lang_3')->orderBy('name_lang_0');
                    break;
            }
        }
        return view('base/index', ['bases' => $bases->paginate(60)]);
    }

    function show(Base $base)
    {
        return view('base/show', ['type_form' => 'show', 'base' => $base]);
    }

    function create()
    {
        return view('base/edit', ['types' => Base::get_types()]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $base = new Base($request->except('_token', '_method'));

        $base->name_lang_0 = $request->name_lang_0;
        $base->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $base->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $base->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";
        $base->names_lang_0 = $request->names_lang_0;
        $base->names_lang_1 = isset($request->names_lang_1) ? $request->names_lang_1 : "";
        $base->names_lang_2 = isset($request->names_lang_2) ? $request->names_lang_2 : "";
        $base->names_lang_3 = isset($request->names_lang_3) ? $request->names_lang_3 : "";

        // у этой команды два предназначения:
        // 1) заменить "on" на "1" при отмеченном checkbox
        // 2) создать новое значение "0" при выключенном checkbox
        // в базе данных информация хранится как "0" или "1"
        $base->is_code_needed = isset($request->is_code_needed) ? "1" : "0";
        $base->is_code_number = isset($request->is_code_number) ? "1" : "0";
        $base->is_limit_sign_code = isset($request->is_limit_sign_code) ? "1" : "0";
        $base->significance_code = $request->significance_code;
        $base->is_code_zeros = isset($request->is_code_zeros) ? "1" : "0";
        $base->is_suggest_code = isset($request->is_suggest_code) ? "1" : "0";
        $base->is_suggest_max_code = isset($request->is_suggest_max_code) ? "1" : "0";
        $base->is_recalc_code = isset($request->is_recalc_code) ? "1" : "0";
        $base->is_required_lst_num_str = isset($request->is_required_lst_num_str) ? "1" : "0";
        $base->is_one_value_lst_str = isset($request->is_one_value_lst_str) ? "1" : "0";
        $base->is_calcname_lst = isset($request->is_calcname_lst) ? "1" : "0";
        $base->is_same_small_calcname = isset($request->is_same_small_calcname) ? "1" : "0";

        $base->digits_num = $request->digits_num;
        $base->sepa_calcname = isset($request->sepa_calcname) ? $request->sepa_calcname : "";
        $base->sepa_same_left_calcname = isset($request->sepa_same_left_calcname) ? $request->sepa_same_left_calcname : "";
        $base->sepa_same_right_calcname = isset($request->sepa_same_left_calcname) ? $request->sepa_same_right_calcname : "";

        // Похожие строки в BaseController.php (functions: store(), edit())
        // и в Base.php (functions: get_types(), type(), type_name())
        // и в Base/edit.blade.php
        switch ($request->vartype) {
            // Список
            case 0:
                $base->type_is_list = true;
                $base->type_is_number = false;
                $base->type_is_string = false;
                $base->type_is_date = false;
                $base->type_is_boolean = false;
                $base->digits_num = 0;
                break;
            // Число
            case 1:
                $base->type_is_list = false;
                $base->type_is_number = true;
                $base->type_is_string = false;
                $base->type_is_date = false;
                $base->type_is_boolean = false;
                $base->is_code_needed = "0";
                $base->is_one_value_lst_str = "0";
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
            // Строка
            case 2:
                $base->type_is_list = false;
                $base->type_is_number = false;
                $base->type_is_string = true;
                $base->type_is_date = false;
                $base->type_is_boolean = false;
                $base->is_code_needed = "0";
                $base->digits_num = 0;
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
            // Дата
            case 3:
                $base->type_is_list = false;
                $base->type_is_number = false;
                $base->type_is_string = false;
                $base->type_is_date = true;
                $base->type_is_boolean = false;
                $base->is_code_needed = "0";
                $base->digits_num = 0;
                $base->is_required_lst_num_str = "0";
                $base->is_one_value_lst_str = "0";
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
            // Логический
            case 4:
                $base->type_is_list = false;
                $base->type_is_number = false;
                $base->type_is_string = false;
                $base->type_is_date = false;
                $base->type_is_boolean = true;
                $base->is_code_needed = "0";
                $base->digits_num = 0;
                $base->is_required_lst_num_str = "0";
                $base->is_one_value_lst_str = "0";
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
        }
        if ($base->is_code_needed == "0") {
            $base->is_code_number = "0";
        }
        if ($base->is_code_number == "0") {
            $base->is_limit_sign_code = "0";
            $base->is_suggest_code = "0";
            $base->is_recalc_code = "0";
        };
        // В принципе - необязательно, а так - нужно
        if ($base->is_suggest_code == "0") {
            $base->is_suggest_max_code = "0";
        };
        // Нужно, если пользователь введет 0 в поле $base->significance_code при $base->is_limit_sign_code = true
        if($base->significance_code == 0){
            $base->is_limit_sign_code = "0";
        }
        // Нужно
        if ($base->is_limit_sign_code == "0") {
            $base->significance_code = 0;
            $base->is_code_zeros = 0;
        };

        $base->save();

        return redirect()->route('base.index');
    }

    function update(Request $request, Base $base)
    {
        if (!(($base->name_lang_0 == $request->name_lang_0) && ($base->name_lang_0 == $request->name_lang_0))) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $base->fill($data);

        $base->name_lang_0 = $request->name_lang_0;
        $base->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $base->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $base->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";
        $base->names_lang_0 = $request->names_lang_0;
        $base->names_lang_1 = isset($request->names_lang_1) ? $request->names_lang_1 : "";
        $base->names_lang_2 = isset($request->names_lang_2) ? $request->names_lang_2 : "";
        $base->names_lang_3 = isset($request->names_lang_3) ? $request->names_lang_3 : "";

        $base->digits_num = $request->digits_num;

        // у этой команды два предназначения:
        // 1) заменить "on" на "1" при отмеченном checkbox
        // 2) создать новое значение "0" при выключенном checkbox
        // в базе данных информация хранится как "0" или "1"
        $base->is_code_needed = isset($request->is_code_needed) ? "1" : "0";
        $base->is_code_number = isset($request->is_code_number) ? "1" : "0";
        $base->is_limit_sign_code = isset($request->is_limit_sign_code) ? "1" : "0";
        $base->significance_code = $request->significance_code;
        $base->is_code_zeros = isset($request->is_code_zeros) ? "1" : "0";
        $base->is_suggest_code = isset($request->is_suggest_code) ? "1" : "0";
        $base->is_suggest_max_code = isset($request->is_suggest_max_code) ? "1" : "0";
        $base->is_recalc_code = isset($request->is_recalc_code) ? "1" : "0";
        $base->is_required_lst_num_str = isset($request->is_required_lst_num_str) ? "1" : "0";
        $base->is_one_value_lst_str = isset($request->is_one_value_lst_str) ? "1" : "0";
        $base->is_calcname_lst = isset($request->is_calcname_lst) ? "1" : "0";
        $base->is_same_small_calcname = isset($request->is_same_small_calcname) ? "1" : "0";

        $base->digits_num = $request->digits_num;
        $base->sepa_calcname = isset($request->sepa_calcname) ? $request->sepa_calcname : "";
        $base->sepa_same_left_calcname = isset($request->sepa_same_left_calcname) ? $request->sepa_same_left_calcname : "";
        $base->sepa_same_right_calcname = isset($request->sepa_same_left_calcname) ? $request->sepa_same_right_calcname : "";

        // Похожие строки в BaseController.php (functions: store(), edit())
        // и в Base.php (functions: get_types(), type(), type_name())
        // и в Base/edit.blade.php
        switch ($request->vartype) {
            // Список
            case 0:
                $base->type_is_list = true;
                $base->type_is_number = false;
                $base->type_is_string = false;
                $base->type_is_date = false;
                $base->type_is_boolean = false;
                $base->digits_num = 0;
                break;
            // Число
            case 1:
                $base->type_is_list = false;
                $base->type_is_number = true;
                $base->type_is_string = false;
                $base->type_is_date = false;
                $base->type_is_boolean = false;
                $base->is_code_needed = "0";
                $base->is_one_value_lst_str = "0";
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
            // Строка
            case 2:
                $base->type_is_list = false;
                $base->type_is_number = false;
                $base->type_is_string = true;
                $base->type_is_date = false;
                $base->type_is_boolean = false;
                $base->is_code_needed = "0";
                $base->digits_num = 0;
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
            // Дата
            case 3:
                $base->type_is_list = false;
                $base->type_is_number = false;
                $base->type_is_string = false;
                $base->type_is_date = true;
                $base->type_is_boolean = false;
                $base->is_code_needed = "0";
                $base->digits_num = 0;
                $base->is_required_lst_num_str = "0";
                $base->is_one_value_lst_str = "0";
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
            // Логический
            case 4:
                $base->type_is_list = false;
                $base->type_is_number = false;
                $base->type_is_string = false;
                $base->type_is_date = false;
                $base->type_is_boolean = true;
                $base->is_code_needed = "0";
                $base->digits_num = 0;
                $base->is_required_lst_num_str = "0";
                $base->is_one_value_lst_str = "0";
                $base->is_calcname_lst = "0";
                $base->sepa_calcname = "";
                $base->is_same_small_calcname = "0";
                $base->sepa_same_left_calcname = "";
                $base->sepa_same_right_calcname = "";
                break;
        }
        if ($base->is_code_needed == "0") {
            $base->is_code_number = "0";
        }
        if ($base->is_code_number == "0") {
            $base->is_limit_sign_code = "0";
            $base->is_suggest_code = "0";
            $base->is_recalc_code = "0";
        };
        // Предлагать расчитать код при добавлении записи
        // В принципе - необязательно, а так - нужно
        if ($base->is_suggest_code == "0") {
            $base->is_suggest_max_code = "0";
        };
        // Нужно, если пользователь введет 0 в поле $base->significance_code при $base->is_limit_sign_code = true
        if($base->significance_code == 0){
            $base->is_limit_sign_code = "0";
        }
        // Ограничить количество вводимых цифр
        // Нужно
        if ($base->is_limit_sign_code == "0") {
            $base->significance_code = 0;
            $base->is_code_zeros = 0;
        };

        $base->save();

        return redirect()->route('base.index');
    }

    function edit(Base $base)
    {
        return view('base/edit', ['base' => $base, 'types' => Base::get_types()]);
    }

    function delete_question(Base $base)
    {
        return view('base/show', ['type_form' => 'delete_question', 'base' => $base]);
    }

    function delete(Base $base)
    {
        $base->delete();
        return redirect()->route('base.index');
    }

//    // Вариант 1
//    static function form_tree($id)
//    {
//        $base = Base::find($id);
//        $result = self::form_tree_start($id);
//        if ($result != '') {
//            $result = '<ul type="circle"><li>' . $base->name() . $result . '</li></ul>';
//
//        }
//        return $result;
//    }

    // эти функции похожи
    // вычисляемый link_id уникальный в этих функциях должен быть
    static function form_tree($base_id)
    {
        $base = Base::find($base_id);
        $list = array();
        $path_previous = $base->name();
        $result = self::form_tree_start($list, $base_id, $path_previous);
        if ($result != '') {
            $result = '<ul type="circle"><li>' . $base->name() . $result . '</li></ul>';

        }
        return $result;
    }

    static function form_tree_start(&$list, $id, $path_previous)
    {
        $result = '<ul type="circle">';
        // расчетные поля не включаются в список
        //$links = Link::all()->where('child_base_id', $id)->where('parent_is_parent_related', false)->sortBy('parent_base_number');
        $links = Link::all()->where('child_base_id', $id)->sortBy('parent_base_number');
        // эти строки нужны
        if (count($links) == 0) {
            return '';
        }
        if (!(array_search($id, $list) === false)) {
            return '';
        }
        ////////////////////
        $list[count($list)] = $id;
        foreach ($links as $link) {
            $rs = '';
            $pt = '';
            $str = '';
            $path = $path_previous . ' \\' . $link->parent_label();// . $pt;
            // чтобы не было бесконечного цикла
            if ($link->parent_base_id != $id) {
                $str = self::form_tree_start($list, $link->parent_base_id, $path);
            };
            $result = $result . '<li>' . $link->id . ' ' . $link->child_base_id . ' ' . $link->parent_base_id . ' ' . $path
                . $str . '</li>';
        }


        $result = $result . "</ul>";
        return $result;
    }

    static function get_array_bases_tree_ul($base_id)
    {
        $base = Base::find($base_id);
        $list = array();
        $result_index = 0;
        $result_keys = array();
        $result_values = array();
        $path_previous = $base->name();
        self::get_array_bases_tree_start($list, $result_index, $result_keys, $result_values, $base_id, $path_previous);
        $result = '<ul type="circle">';
        foreach ($result_values as $base) {
            $result = $result . '<li>' . $base . '</li>';
        }
        $result = $result . '</ul>';
        return $result;
    }

    static function get_array_bases_tree_options($base_id)
    {
        $base = Base::find($base_id);
        $list = array();
        $result_index = 0;
        $result_keys = array();
        $result_values = array();
        $path_previous = $base->name();
        self::get_array_bases_tree_start($list, $result_index, $result_keys, $result_values, $base_id, $path_previous);
        $result = '';
        foreach ($result_values as $key => $value) {
            $result = $result . '<option value="' . $result_keys[$key] . '">' . $value . '</option>';
        }
        return $result;
    }

    static function get_array_bases_tree_start(&$list, &$result_index, &$result_keys, &$result_values, $id, $path_previous)
    {
        $result = '<ul type="circle">';
        // расчетные поля не включаются в список
        $links = Link::all()->where('child_base_id', $id)->where('parent_is_parent_related', false)->sortBy('parent_base_number');
        // эти строки нужны
        if (count($links) == 0) {
            return;
        }
        if (!(array_search($id, $list) === false)) {
            return;
        }
        ////////////////////
        $list[count($list)] = $id;
        foreach ($links as $link) {
            $rs = '';
            $pt = '';
            $str = '';
            $path = $path_previous . ' \\' . $link->parent_label();
            $result_keys[$result_index] = $link->id;
            $result_values[$result_index] = $path;
            $result_index = $result_index + 1;
            // чтобы не было бесконечного цикла
            if ($link->parent_base_id != $id) {
                self::get_array_bases_tree_start($list, $result_index, $result_keys, $result_values, $link->parent_base_id, $path);
            };
        }
        return;
    }

    // возвращает "существуют ли переданный $par_link в уникальном маршруте $links из $link-id"
    static function get_par_link_in_array_bases_tree_routes($base_id, $link_id, $par_link)
    {
        $arr = self::get_array_bases_tree_routes($base_id, $link_id, false);
        $result = false;
        if ($arr != null) {
            $result = in_array($par_link, $arr);  // true или false
        }
        return $arr;  // true или false
    }

    // возвращает маршрут $links из link_id для доступа к объекту для переданного параметра (link_id уникальный д.б. в функции)
    static function get_array_bases_tree_routes($base_id, $link_id, $child_to_parent)  // "boolean $child_to_parent"
    {
        $list = array();
        $route_previous = '';
        $routes = array();
        $result = null;
        self::get_array_bases_tree_routes_start($list, $base_id, $routes, $route_previous);

        foreach ($routes as $route) {
            $arr = explode(" ", $route);
            // удаляет первый элемент массива, после команды explode() создается первый элемент массива нулевой
            array_shift($arr);
            // если последний элемент массива равен нужному link_id
            if ($arr[count($arr) - 1] == $link_id) {
                if (!$child_to_parent) {
                    //  возвращает массив с элементами в обратном порядке
                    $arr = array_reverse($arr);
                }
                $result = $arr;
                break;
            }
        }
        return $result;
    }

    static function get_array_bases_tree_routes_start(&$list, $id, &$routes, $route_previous)
    {
        // расчетные поля не включаются в список
        $links = Link::all()->where('child_base_id', $id)->where('parent_is_parent_related', false)->sortBy('parent_base_number');
        // эти строки нужны
        if (count($links) == 0) {
            return;
        }
        if (!(array_search($id, $list) === false)) {
            return;
        }
        ////////////////////
        $list[count($list)] = $id;
        foreach ($links as $link) {
            $route = $route_previous . ' ' . $link->id;
            $routes[count($routes)] = $route;
            // чтобы не было бесконечного цикла
            if ($link->parent_base_id != $id) {
                self::get_array_bases_tree_routes_start($list, $link->parent_base_id, $routes, $route);
            };
        }
        return;
    }
    ////////////////////////////////////////
    // Возвращает имя поля ноименование в зависимости от текущего языка
    static function field_name()
    {
        $result = "";  // нужно, не удалять
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = 'name_lang_' . $index;
        }
        if ($result == "") {
            $result = 'name_lang_0';
        }
        return $result;
    }

    function getBasesAll(){
  $bases = $bases = Base::orderBy('name_lang_0')->get();

        print(json_encode($bases));


    }

}
