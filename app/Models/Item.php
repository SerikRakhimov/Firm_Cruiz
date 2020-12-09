<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    protected $fillable = ['base_id', 'code', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'];

    function base()
    {
        return $this->belongsTo(Base::class, 'base_id');
    }

    function name()
    {
        $result = "";  // нужно, не удалять

//        //этот вариант тоже работает, но второй вариант предпочтительней
//        if ($this->base->type_is_date()) {
//            $result = date_create($this->name_lang_0)->Format(trans('main.format_date'));
//        } else {
//            $index = array_search(session('locale'), session('glo_menu_save'));
//            if ($index !== false) {   // '!==' использовать, '!=' не использовать
//                $result = $this['name_lang_' . $index];
//            }
//        }
        $base_find = $this->base;
        if ($base_find) {
            if ($base_find->type_is_date()) {
                // "$this->name_lang_0 ??..." нужно, в случае если в $this->name_lang_0 хранится не дата
//               $result = $this->name_lang_0 ? date_create($this->name_lang_0)->Format(trans('main.format_date')):'';
                if (($timestamp = date_create($this->name_lang_0)) === false) {
                    $result = $this->name_lang_0;
                } else {
                    $result = date_create($this->name_lang_0)->Format(trans('main.format_date'));
                }
            } elseif ($base_find->type_is_boolean()) {
                //    Похожие строки в Base.php
                $result = $this->name_lang_0 == "1" ? html_entity_decode('	&#9745;')
                    : ($this->name_lang_0 == "0" ? html_entity_decode('&#65794;') : trans('main.empty'));
                //
            } else {
                $index = array_search(session('locale'), session('glo_menu_save'));
                if ($index !== false) {   // '!==' использовать, '!=' не использовать
                    $result = $this['name_lang_' . $index];
                }
            }
        }
//        if ($result == "") {
//            $result = $this->name_lang_0;
//        }
        return $result;
    }

    function names()
    {
        $res_array = array();
        $d = session('glo_menu_main');
        foreach (session('glo_menu_main') as $lang_key => $lang_value) {
            $name = "";  // нужно, не удалять
            $base_find = $this->base;
            if ($base_find) {
                // Эта строка нужна, не удалять
                $name = $this['name_lang_' . $lang_key];
                if ($base_find->type_is_date()) {
                    $name = date_create($name)->Format(trans('main.format_date'));
                } elseif ($base_find->type_is_boolean()) {
                    //    Похожие строки в Base.php
                    $name = $name == "1" ? html_entity_decode('	&#9745;')
                        : ($name == "0" ? html_entity_decode('&#65794;') : trans('main.empty'));
                    //
                }
            }
            $res_array[$lang_key] = $name;
        }
        return $res_array;
    }

    function info()
    {
        return $this->name();
    }

    function code_add_zeros()
    {
        if($this->base->is_code_zeros == true){
        // Дополнить код слева нулями
        $this->code = str_pad($this->code, $this->base->significance_code, '0', STR_PAD_LEFT);}
    }

    function info_full()
    {
        return trans('main.item') . " (" . $this->id . ") _ " . $this->base->name() . " _ " . $this->name();
    }

    function child_mains()
    {
        return $this->hasMany(Main::class, 'child_item_id');
    }

    function parent_mains()
    {
        return $this->hasMany(Main::class, 'parent_item_id');
    }

}
