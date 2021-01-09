<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    protected function rules()
    {
//        return [
//            'child_base_id' => 'exists:bases,id|unique_with: links, child_base_id, parent_base_id, parent_label_lang_0',
//            'parent_base_id' => 'exists:bases,id|unique_with: links, child_base_id, parent_base_id, parent_label_lang_0',
//            'child_label_lang_0' => 'unique_with: links, child_base_id, parent_base_id, child_label_lang_0, parent_label_lang_0',
//            'parent_label_lang_0' => 'unique_with: links, child_base_id, parent_base_id, child_label_lang_0, parent_label_lang_0',
//        ];
        return [
            'child_base_id' => 'exists:bases,id',
            'parent_base_id' => 'exists:bases,id',
        ];
    }

    function index()
    {
        $links = null;
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    $links = Link::orderBy('child_base_id')->orderBy('parent_base_number')->orderBy('parent_label_lang_0');
                    break;
                case 1:
                    $links = Link::orderBy('child_base_id')->orderBy('parent_base_number')->orderBy('parent_label_lang_1')->orderBy('parent_label_lang_0');
                    break;
                case 2:
                    $links = Link::orderBy('child_base_id')->orderBy('parent_base_number')->orderBy('parent_label_lang_2')->orderBy('parent_label_lang_0');
                    break;
                case 3:
                    $links = Link::orderBy('child_base_id')->orderBy('parent_base_number')->orderBy('parent_label_lang_3')->orderBy('parent_label_lang_0');
                    break;
            }
        }
        return view('link/index', ['links' => $links->paginate(60)]);
    }

    function base_index(Base $base)
    {
        $items = null;
        session(['links' => ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . request()->path()]);
        return view('link/base_index', ['base' => $base, 'links' => Link::where('child_base_id', $base->id)->orderBy('parent_base_number')->get()]);
    }


    function show(Link $link)
    {
        return view('link/show', ['type_form' => 'show', 'link' => $link]);
    }

    function create(Base $base)
    {
        return view('link/edit', ['bases' => Base::where('template_id', $base->template_id)->get()]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $link = new Link($request->except('_token', '_method'));

        $link->child_base_id = $request->child_base_id;
        $link->parent_base_id = $request->parent_base_id;
        $link->child_label_lang_0 = isset($request->child_label_lang_0) ? $request->child_label_lang_0 : "";
        $link->child_label_lang_1 = isset($request->child_label_lang_1) ? $request->child_label_lang_1 : "";
        $link->child_label_lang_2 = isset($request->child_label_lang_2) ? $request->child_label_lang_2 : "";
        $link->child_label_lang_3 = isset($request->child_label_lang_3) ? $request->child_label_lang_3 : "";
        $link->child_labels_lang_0 = isset($request->child_labels_lang_0) ? $request->child_labels_lang_0 : "";
        $link->child_labels_lang_1 = isset($request->child_labels_lang_1) ? $request->child_labels_lang_1 : "";
        $link->child_labels_lang_2 = isset($request->child_labels_lang_2) ? $request->child_labels_lang_2 : "";
        $link->child_labels_lang_3 = isset($request->child_labels_lang_3) ? $request->child_labels_lang_3 : "";
        $link->parent_base_number = $request->parent_base_number;
        $link->parent_label_lang_0 = isset($request->parent_label_lang_0) ? $request->parent_label_lang_0 : "";
        $link->parent_label_lang_1 = isset($request->parent_label_lang_1) ? $request->parent_label_lang_1 : "";
        $link->parent_label_lang_2 = isset($request->parent_label_lang_2) ? $request->parent_label_lang_2 : "";
        $link->parent_label_lang_3 = isset($request->parent_label_lang_3) ? $request->parent_label_lang_3 : "";
        $link->parent_is_enter_refer = isset($request->parent_is_enter_refer) ? true : false;
        $link->parent_is_calcname = isset($request->parent_is_calcname) ? true : false;
        $link->parent_is_left_calcname = isset($request->parent_is_left_calcname) ? true : false;
        $link->parent_calcname_prefix_lang_0 = isset($request->parent_calcname_prefix_lang_0) ? $request->parent_calcname_prefix_lang_0 : "";
        $link->parent_calcname_prefix_lang_1 = isset($request->parent_calcname_prefix_lang_1) ? $request->parent_calcname_prefix_lang_1 : "";
        $link->parent_calcname_prefix_lang_2 = isset($request->parent_calcname_prefix_lang_2) ? $request->parent_calcname_prefix_lang_2 : "";
        $link->parent_calcname_prefix_lang_3 = isset($request->parent_calcname_prefix_lang_3) ? $request->parent_calcname_prefix_lang_3 : "";
        $link->parent_is_numcalc = isset($request->parent_is_numcalc) ? true : false;
        $link->parent_is_nc_viewonly = isset($request->parent_is_nc_viewonly) ? true : false;
        $link->parent_is_nc_screencalc = isset($request->parent_is_nc_screencalc) ? true : false;
        $link->parent_is_nc_parameter = isset($request->parent_is_nc_parameter) ? true : false;

        $link->parent_is_small_calcname = isset($request->parent_is_small_calcname) ? true : false;
        if ($link->parent_is_calcname == false) {
            $link->parent_is_small_calcname = false;
        }
        if ($link->child_label_lang_0 == "") {
            $link->child_label_lang_0 = $link->child_base->name_lang_0;
        }
        if ($link->child_label_lang_1 == "") {
            $link->child_label_lang_1 = $link->child_base->name_lang_1;
        }
        if ($link->child_label_lang_2 == "") {
            $link->child_label_lang_2 = $link->child_base->name_lang_2;
        }
        if ($link->child_label_lang_3 == "") {
            $link->child_label_lang_3 = $link->child_base->name_lang_3;
        }

        if ($link->child_labels_lang_0 == "") {
            $link->child_labels_lang_0 = $link->child_base->names_lang_0;
        }
        if ($link->child_labels_lang_1 == "") {
            $link->child_labels_lang_1 = $link->child_base->name_lang_1;
        }
        if ($link->child_labels_lang_2 == "") {
            $link->child_labels_lang_2 = $link->child_base->name_lang_2;
        }
        if ($link->child_labels_lang_3 == "") {
            $link->child_labels_lang_3 = $link->child_base->name_lang_3;
        }

        if ($link->parent_label_lang_0 == "") {
            $link->parent_label_lang_0 = $link->parent_base->name_lang_0;
        }
        if ($link->parent_label_lang_1 == "") {
            $link->parent_label_lang_1 = $link->parent_base->name_lang_1;
        }
        if ($link->parent_label_lang_2 == "") {
            $link->parent_label_lang_2 = $link->parent_base->name_lang_2;
        }
        if ($link->parent_label_lang_3 == "") {
            $link->parent_label_lang_3 = $link->parent_base->name_lang_3;
        }

        $link->parent_is_parent_related = isset($request->parent_is_parent_related) ? true : false;
        if ($link->parent_is_parent_related) {
            $link->parent_parent_related_start_link_id = $request->parent_parent_related_start_link_id;
            $link->parent_parent_related_result_link_id = $request->parent_parent_related_result_link_id;
        } else {
            $link->parent_parent_related_start_link_id = 0;
            $link->parent_parent_related_result_link_id = 0;
        }

        $link->parent_is_child_related = isset($request->parent_is_child_related) ? true : false;
        if ($link->parent_is_child_related) {
            $link->parent_child_related_start_link_id = $request->parent_child_related_start_link_id;
            $link->parent_child_related_result_link_id = $request->parent_child_related_result_link_id;
        } else {
            $link->parent_child_related_start_link_id = 0;
            $link->parent_child_related_result_link_id = 0;
        }
        if ($link->parent_base->type_is_number == false) {
            $link->parent_is_numcalc = 0;
            $link->parent_is_nc_viewonly = 0;
            $link->parent_is_nc_parameter = 0;
        }
        if ($link->parent_is_numcalc == false) {
            $link->parent_is_nc_screencalc = 0;
        }

        $link->save();

        return redirect()->route('link.base_index', ['base' => $link->child_base, 'links' => Link::where('child_base_id', $link->child_base_id)->orderBy('parent_base_number')->get()]);
    }

    function update(Request $request, Link $link)
    {
        // Если данные изменились - выполнить проверку
//        if (!(($link->child_base_id == $request->child_base_id)
//            and ($link->child_label_lang_0 == $request->child_label_lang_0)
//            and ($link->parent_base_id == $request->parent_base_id)
//            and ($link->parent_label_lang_0 == $request->parent_label_lang_0)))
        if (!(($link->child_base_id == $request->child_base_id)
            and ($link->parent_base_id == $request->parent_base_id))) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');
        $link->fill($data);


        $link->child_base_id = $request->child_base_id;
        $link->parent_base_id = $request->parent_base_id;
        $link->child_label_lang_0 = isset($request->child_label_lang_0) ? $request->child_label_lang_0 : "";
        $link->child_label_lang_1 = isset($request->child_label_lang_1) ? $request->child_label_lang_1 : "";
        $link->child_label_lang_2 = isset($request->child_label_lang_2) ? $request->child_label_lang_2 : "";
        $link->child_label_lang_3 = isset($request->child_label_lang_3) ? $request->child_label_lang_3 : "";
        $link->child_labels_lang_0 = isset($request->child_labels_lang_0) ? $request->child_labels_lang_0 : "";
        $link->child_labels_lang_1 = isset($request->child_labels_lang_1) ? $request->child_labels_lang_1 : "";
        $link->child_labels_lang_2 = isset($request->child_labels_lang_2) ? $request->child_labels_lang_2 : "";
        $link->child_labels_lang_3 = isset($request->child_labels_lang_3) ? $request->child_labels_lang_3 : "";
        $link->parent_base_number = $request->parent_base_number;
        $link->parent_label_lang_0 = isset($request->parent_label_lang_0) ? $request->parent_label_lang_0 : "";
        $link->parent_label_lang_1 = isset($request->parent_label_lang_1) ? $request->parent_label_lang_1 : "";
        $link->parent_label_lang_2 = isset($request->parent_label_lang_2) ? $request->parent_label_lang_2 : "";
        $link->parent_label_lang_3 = isset($request->parent_label_lang_3) ? $request->parent_label_lang_3 : "";
        $link->parent_is_enter_refer = isset($request->parent_is_enter_refer) ? true : false;
        $link->parent_is_calcname = isset($request->parent_is_calcname) ? true : false;
        $link->parent_is_left_calcname = isset($request->parent_is_left_calcname) ? true : false;
        $link->parent_calcname_prefix_lang_0 = isset($request->parent_calcname_prefix_lang_0) ? $request->parent_calcname_prefix_lang_0 : "";
        $link->parent_calcname_prefix_lang_1 = isset($request->parent_calcname_prefix_lang_1) ? $request->parent_calcname_prefix_lang_1 : "";
        $link->parent_calcname_prefix_lang_2 = isset($request->parent_calcname_prefix_lang_2) ? $request->parent_calcname_prefix_lang_2 : "";
        $link->parent_calcname_prefix_lang_3 = isset($request->parent_calcname_prefix_lang_3) ? $request->parent_calcname_prefix_lang_3 : "";
        $link->parent_is_numcalc = isset($request->parent_is_numcalc) ? true : false;
        $link->parent_is_nc_viewonly = isset($request->parent_is_nc_viewonly) ? true : false;
        $link->parent_is_nc_screencalc = isset($request->parent_is_nc_screencalc) ? true : false;
        $link->parent_is_nc_parameter = isset($request->parent_is_nc_parameter) ? true : false;

        $link->parent_is_small_calcname = isset($request->parent_is_small_calcname) ? true : false;
        if ($link->parent_is_calcname == false) {
            $link->parent_is_small_calcname = false;
        }
        if ($link->child_label_lang_0 == "") {
            $link->child_label_lang_0 = $link->child_base->name_lang_0;
        }
        if ($link->child_label_lang_1 == "") {
            $link->child_label_lang_1 = $link->child_base->name_lang_1;
        }
        if ($link->child_label_lang_2 == "") {
            $link->child_label_lang_2 = $link->child_base->name_lang_2;
        }
        if ($link->child_label_lang_3 == "") {
            $link->child_label_lang_3 = $link->child_base->name_lang_3;
        }

        if ($link->child_labels_lang_0 == "") {
            $link->child_labels_lang_0 = $link->child_base->names_lang_0;
        }
        if ($link->child_labels_lang_1 == "") {
            $link->child_labels_lang_1 = $link->child_base->name_lang_1;
        }
        if ($link->child_labels_lang_2 == "") {
            $link->child_labels_lang_2 = $link->child_base->name_lang_2;
        }
        if ($link->child_labels_lang_3 == "") {
            $link->child_labels_lang_3 = $link->child_base->name_lang_3;
        }

        if ($link->parent_label_lang_0 == "") {
            $link->parent_label_lang_0 = $link->parent_base->name_lang_0;
        }
        if ($link->parent_label_lang_1 == "") {
            $link->parent_label_lang_1 = $link->parent_base->name_lang_1;
        }
        if ($link->parent_label_lang_2 == "") {
            $link->parent_label_lang_2 = $link->parent_base->name_lang_2;
        }
        if ($link->parent_label_lang_3 == "") {
            $link->parent_label_lang_3 = $link->parent_base->name_lang_3;
        }

        $link->parent_is_parent_related = isset($request->parent_is_parent_related) ? true : false;
        if ($link->parent_is_parent_related) {
            $link->parent_parent_related_start_link_id = $request->parent_parent_related_start_link_id;
            $link->parent_parent_related_result_link_id = $request->parent_parent_related_result_link_id;
        } else {
            $link->parent_parent_related_start_link_id = 0;
            $link->parent_parent_related_result_link_id = 0;
        }

        $link->parent_is_child_related = isset($request->parent_is_child_related) ? true : false;
        if ($link->parent_is_child_related) {
            $link->parent_child_related_start_link_id = $request->parent_child_related_start_link_id;
            $link->parent_child_related_result_link_id = $request->parent_child_related_result_link_id;
        } else {
            $link->parent_child_related_start_link_id = 0;
            $link->parent_child_related_result_link_id = 0;
        }
        if ($link->parent_base->type_is_number == false) {
            $link->parent_is_numcalc = 0;
            $link->parent_is_nc_viewonly = 0;
            $link->parent_is_nc_parameter = 0;
        }
        if ($link->parent_is_numcalc == false) {
            $link->parent_is_nc_screencalc = 0;
        }

        $link->save();

        return redirect()->route('link.base_index', ['base' => $link->child_base, 'links' => Link::where('child_base_id', $link->child_base_id)->orderBy('parent_base_number')->get()]);
    }

    function edit(Link $link, Base $base)
    {
        return view('link/edit', ['link' => $link, 'bases' =>  Base::where('template_id', $base->template_id)->get()]);
    }

    function delete_question(Link $link)
    {
        return view('link/show', ['type_form' => 'delete_question', 'link' => $link]);
    }

    function delete(Link $link)
    {
        $link->delete();
        return redirect()->route('link.index');
    }

    static function get_parent_parent_related_start_link_id(Base $base, Link $link_current = null)
    {
        $result_parent_parent_related_start_link_id_options = '';
        if ($base != null) {
            // список links по выбранному base_id
            $links = Link::all()->where('child_base_id', $base->id)->sortBy('parent_base_number');
            // при корректировке записи текущую запись не отображать в списке
            if ($link_current) {
                $links = $links->where('id', '!=', $link_current->id);
            }
            //$links = $links->get();  // не использовать - ошибку дает

            foreach ($links as $link) {
                // есть ли отношения child-parent
                if (!$link->parent_base->child_links->IsEmpty()) {
                    $result_parent_parent_related_start_link_id_options = $result_parent_parent_related_start_link_id_options
                        . "<option value='" . $link->id . "'>" . $link->parent_label() . "</option>";
                }
            }
        }
        return [
            'result_parent_parent_related_start_link_id_options' => $result_parent_parent_related_start_link_id_options,
        ];
    }

// функции get_parent_parent_related_start_link_id() и get_parent_child_related_start_link_id() похожи
// разница в наличии команды "->where('parent_is_parent_related', false)" в get_parent_child_related_start_link_id
    static function get_parent_child_related_start_link_id(Base $base, Link $link_current = null)
    {
        $result_parent_child_related_start_link_id_options = '';
        if ($base != null) {
            // список links по выбранному base_id
            // исключить вычисляемые поля
            $links = Link::all()
                ->where('parent_is_parent_related', false)
                ->where('child_base_id', $base->id)->sortBy('parent_base_number');
            // при корректировке записи текущую запись не отображать в списке
            if ($link_current) {
                $links = $links->where('id', '!=', $link_current->id);
            }
            //$links = $links->get();  // не ставить - ошибку дает

            // исключим уже существующие поля для фильтрования
            // у одного поля для фильтрования - один маршрут д.б.
            foreach ($links as $link) {
                if ($link->parent_is_child_related == true) {
                    if ($link != $link_current) {
                        $links = $links->where('id', '!=', $link->parent_child_related_start_link_id);
                    }
                }
            }

            foreach ($links as $link) {
                // есть ли отношения child-parent
                if (!$link->parent_base->child_links->IsEmpty()) {
                    $result_parent_child_related_start_link_id_options = $result_parent_child_related_start_link_id_options
                        . "<option value='" . $link->id . "'>" . $link->parent_label() . "</option>";
                }
            }
        }
        return [
            'result_parent_child_related_start_link_id_options' => $result_parent_child_related_start_link_id_options,
        ];
    }

    static function get_tree_from_link_id(Link $link_start)
    {
        $result_parent_parent_related_result_link_id_options = '';
        if ($link_start != null) {

            $result_parent_parent_related_result_link_id_options = BaseController::get_array_bases_tree_options($link_start->parent_base_id);
        }
        return [
            'result_parent_parent_related_result_link_id_options' => $result_parent_parent_related_result_link_id_options,
        ];
    }

    static function get_parent_base_id_from_link_id(Link $link)
    {
        $parent_base_id = '';
        $parent_base_name = '';
        if ($link != null) {
            $parent_base_id = $link->parent_base_id;
            $parent_base_name = $link->parent_base->name();
        }
        return [
            'parent_base_id' => $parent_base_id,
            'parent_base_name' => $parent_base_name,
        ];
    }

    // возвращает маршрут $link_ids по вычисляемым полям
    //                              --------------------
    // до первого найденного постоянного link_id ($const_link_id_start)
    // для маршрута используется поле $link->parent_parent_related_start_link_id
    static function get_link_ids_from_calc_link($link_init)
    {
        // максимальное количество итераций при возможном зацикливании
        $maxi = 1000;
        $const_link_id_start = null;
        $const_link_start = null;
        $link_ids = array();
        $link = $link_init;
        $i = 0;
        // проверка, если link - вычисляемое поле
        while (($link->parent_is_parent_related == true) && ($i < $maxi) && $link) {
            // добавление элемента в конец массива
            array_unshift($link_ids, $link->id);
            $i = $i + 1;
            $link = Link::find($link->parent_parent_related_start_link_id);
        }
        // если зацикливание или $link не найден - возвратить null и пустой массив
        if (($i >= $maxi) || (!$link)) {
            $const_link_id_start = null;
            $const_link_start = null;
            $link_ids = array();
        }
        // найти первый начальный $link невычисляемый
        if (count($link_ids) > 0) {
            $const_link_id_start = $link->id;  // "$link->id" использовать
            $const_link_start = $link;  // "$link" использовать
        }
        return [
            'const_link_id_start' => $const_link_id_start,
            'const_link_start' => $const_link_start,
            'link_ids' => $link_ids  // все элементы в $link_ids - вычисляемые поля
        ];
    }

}
