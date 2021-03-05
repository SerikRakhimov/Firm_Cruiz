<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Observers\ItemObserver;

class Item extends Model
{
    protected $fillable = ['base_id', 'project_id', 'updated_user_id', 'code', 'name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'];

    function base()
    {
        return $this->belongsTo(Base::class, 'base_id');
    }

    function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    function created_user()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    function updated_user()
    {
        return $this->belongsTo(User::class, 'updated_user_id');
    }

    function created_user_date()
    {
        return $this->created_user->name() . ", " . $this->created_at->Format(trans('main.format_date'));
    }

    function updated_user_date()
    {
        return $this->updated_user->name() . ", " . $this->updated_at->Format(trans('main.format_date'));
    }

    function created_user_date_time()
    {
        return $this->created_user->name() . ", " . $this->created_at->Format(trans('main.format_date_time'));
    }

    function updated_user_date_time()
    {
        return $this->updated_user->name() . ", " . $this->updated_at->Format(trans('main.format_date_time'));
    }

    function child_mains()
    {
        return $this->hasMany(Main::class, 'child_item_id');
    }

    function parent_mains()
    {
        return $this->hasMany(Main::class, 'parent_item_id');
    }

    function name()
    {
        $result = "";  // нужно, не удалять

//        //этот вариант тоже работает, но второй вариант предпочтительней
//        if ($this->base->type_is_date()) {
//            $result = date_create($this->name_lang_0)->Format(trans('main.format_date'));
//        } else {
//            $index = array_search(App::getLocale(), config('app.locales'));
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
                $index = array_search(App::getLocale(), config('app.locales'));
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
        // массив "glo_menu_main" показывает, что четыре поля наименований хранятся в bases и items
        // ['1', '2', '3', '4'] - тут разницы нет, какие значения хранятся; главное, чтобы что-то хранилось
        $main_array = ['1', '2', '3', '4'];
//        foreach (session('glo_menu_main') as $lang_key => $lang_value) {
            foreach ($main_array as $lang_key => $lang_value) {
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
        if ($this->base->is_code_zeros == true) {
            // Дополнить код слева нулями
            $this->code = str_pad($this->code, $this->base->significance_code, '0', STR_PAD_LEFT);
        }
    }

    function info_full()
    {
        return trans('main.item') . " (" . $this->id . ") _ " . $this->base->name() . " _ " . $this->name();
    }

    // Для типов полей Изображение, Документ
    // '$moderation = true' -  возвращать имя файла, независимо прошло/не прошло модерацию
    function filename($moderation = false)
    {
        $result = $this->name_lang_0;
        if ($this->base->type_is_image() == true) {
            if ($this->created_user_id != Auth::user()->id) {
                if ($moderation == false) {
                    if ($this->base->is_to_moderate_image == true) {
                        // На модерации
                        if ($this->name_lang_1 == "3") {
                            $result = "on_moderation.png";
                        } // Не прошло модерацию
                        elseif ($this->name_lang_1 == "2") {
                            $result = "did_not_pass_the_moderation.png";
                        }
                    }
                }
            }
        }
        return $result;
    }

    // Для типов полей Изображение
    function title_img()
    {
        $result = trans('main.сlick_to_view');
        if ($this->base->type_is_image() == true) {
            if ($this->base->is_to_moderate_image == true) {
                // На модерации
                if ($this->name_lang_1 == "3") {
                    $result = trans('main.on_moderation');
                } // Не прошло модерацию
                elseif ($this->name_lang_1 == "2") {
                    $result = trans('main.did_not_pass_the_moderation');
                    if ($this->name_lang_2 != "") {
                        $result = $result . ": " . $this->name_lang_2;
                    }
                }
            }
        }
        return $result;
    }

    // Возвращает true, если статус =  "не прошло модерацию"  и есть комментарий
    function is_no_moderation_info()
    {
        $result = false;
        if ($this->base->type_is_image() == true) {
            // Показывать для пользователя, создавшего фото
            if ($this->created_user_id == Auth::user()->id) {
                if ($this->base->is_to_moderate_image == true) {
                    // Не прошло модерацию
                    if ($this->name_lang_1 == "2") {
                        $result = trans('main.did_not_pass_the_moderation');
                        if ($this->name_lang_2 != "") {
                            $result = true;
                        }
                    }
                }
            }
        }
        return $result;
    }

    // Для типов полей Изображение
    function status_img()
    {
        $result = "";
        if ($this->base->type_is_image() == true) {
            if ($this->base->is_to_moderate_image == true) {
                // На модерации
                if ($this->name_lang_1 == "3") {
                    $result = trans('main.on_moderation');
                } // Не прошло модерацию
                elseif ($this->name_lang_1 == "2") {
                    $result = trans('main.did_not_pass_the_moderation');
                    if ($this->name_lang_2 != "") {
                        $result = $result . ": " . $this->name_lang_2;
                    }
                    // Прошло модерацию
                } elseif ($this->name_lang_1 == "1") {
                    $result = trans('main.moderated');
                    // Без модерации
                } elseif ($this->name_lang_1 == "0") {
                    $result = trans('main.without_moderation');
                }
            }
        }
        return $result;
    }

    static function get_img_statuses()
    {
        return array(
            "0" => trans('main.without_moderation'),
            "1" => trans('main.moderated'),
            "2" => trans('main.did_not_pass_the_moderation'),
            "3" => trans('main.on_moderation')
        );
    }

    // Для типов полей Изображение, Документ
    function img_doc_exist()
    {
        return $this->name_lang_0 != "";
    }

    // Для типов полей Изображение, Документ
    function numval()
    {
        $value = 0;
        $result = false;
        if ($this->base->type_is_number()) {
            $result = true;
            if ($this->name_lang_0 == "") {
                $value = 0;
            } else {
                $value = strval($this->name_lang_0);
            }
        }
        return ['result' => $result, 'value' => $value];
    }

}
