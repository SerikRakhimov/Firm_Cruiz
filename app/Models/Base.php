<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    protected $fillable = ['name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'];

    function items()
    {
        return $this->hasMany(Item::class, 'base_id');
    }

    function child_links()
    {
        return $this->hasMany(Link::class, 'child_base_id');
    }

    function parent_links()
    {
        return $this->hasMany(Link::class, 'parent_base_id');
    }

    function name()
    {
        $result = "";  // нужно, не удалять
        //$index = array_search(App::getLocale(), session('glo_menu_save'));
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = $this['name_lang_' . $index];
        }
        if ($result == "") {
            $result = $this->name_lang_0;
        }
        return $result;
    }

    function names()
    {
        $result = "";  // нужно, не удалять
//      $index = array_search(App::getLocale(), config('app.locales'));
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = $this['names_lang_' . $index];
        }
        if ($result == "") {
            $result = $this->names_lang_0;
        }
        return $result;
    }

    // Похожие строки в BaseController.php (function store() и edit())
    // и base/edit.blade.php
    // и ModerationController (function index())
    static function get_types()
    {
        return array(
            "0" => trans('main.list'),
            "1" => trans('main.number'),
            "2" => trans('main.string'),
            "3" => trans('main.date'),
            "4" => trans('main.boolean'),
            "5" => trans('main.image'),
            "6" => trans('main.document')
        );
    }

    function type()
    {
        // нужно
        $result = -1;
        if ($this->type_is_list == true) {
            $result = 0;
        } else if ($this->type_is_number == true) {
            $result = 1;
        } else if ($this->type_is_string == true) {
            $result = 2;
        } else if ($this->type_is_date == true) {
            $result = 3;
        } else if ($this->type_is_boolean == true) {
            $result = 4;
        } else if ($this->type_is_image == true) {
            $result = 5;
        } else if ($this->type_is_document == true) {
            $result = 6;
        }
        return $result;
    }

    function type_name()
    {
        $result = "";
        switch ($this->type()) {
            case 0:
                $result = trans('main.list');
                break;
            case 1:
                $result = trans('main.number');
                break;
            case 2:
                $result = trans('main.string');
                break;
            case 3:
                $result = trans('main.date');
                break;
            case 4:
                $result = trans('main.boolean');
                break;
            case 5:
                $result = trans('main.image');
                break;
            case 6:
                $result = trans('main.document');
                break;
        }
        return $result;
    }

    function type_is_list()
    {
        return $this->type_is_list == true;
    }

    function type_is_number()
    {
        return $this->type_is_number == true;
    }

    function type_is_string()
    {
        return $this->type_is_string == true;
    }

    function type_is_date()
    {
        return $this->type_is_date == true;
    }

    function type_is_boolean()
    {
        return $this->type_is_boolean == true;
    }

    function type_is_image()
    {
        return $this->type_is_image == true;
    }

    function type_is_document()
    {
        return $this->type_is_document == true;
    }

    function info()
    {
        return $this->name();
    }

    function info_full()
    {
        return trans('main.base') . " (" . $this->id . ")" . " _ " . $this->name();
    }

    function digits_num_format()
    {
        $result = "";
        if ($this->digits_num == 0) {
            $result = "0";
        } else {
            $result = "0.";
            for ($i = 0; $i < ($this->digits_num - 1); $i++) {
                $result = $result . "0";
            }
            $result = $result . "1";
        }
        return $result;
    }


//    Похожие строки в Item.php
    function name_is_required_lst_num_str_img_doc()
    {
        return $this->is_required_lst_num_str_img_doc == "1" ? html_entity_decode('	&#9745;')
            : ($this->is_required_lst_num_str_img_doc == "0" ? html_entity_decode('&#10065;') : trans('main.empty'));
    }

    function name_is_one_value_lst_str()
    {
        return $this->is_one_value_lst_str == "1" ? html_entity_decode('	&#9745;')
            : ($this->is_one_value_lst_str == "0" ? html_entity_decode('&#10065;') : trans('main.empty'));
    }

    function name_is_calcname_lst()
    {
        return $this->is_calcname_lst == "1" ? html_entity_decode('	&#9745;')
            : ($this->is_calcname_lst == "0" ? html_entity_decode('&#10065;') : trans('main.empty'));
    }

    function name_is_same_small_calcname()
    {
        return $this->is_same_small_calcname == "1" ? html_entity_decode('	&#9745;')
            : ($this->is_same_small_calcname == "0" ? html_entity_decode('&#10065;') : trans('main.empty'));
    }

    function number_format()
    {
        $sg = 0;
        if ($this->significance_code > 0) {
            $sg = $this->significance_code;
        } else {
            // Const "15" используется и в ext_edit.blade.php
            $sg = 15;
        }
        return str_repeat('9', $sg);
    }

}
