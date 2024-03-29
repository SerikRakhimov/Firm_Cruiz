<?php

namespace App\Models;

use App\Http\Controllers\GlobalController;
use App\Http\Controllers\ItemController;
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

    //    Похожие строки в GlobalController.php
    function created_user_date()
    {
        return $this->created_user->name() . ", " . $this->created_at->Format(trans('main.format_date')) . ", " . $this->created_user->email;
    }

    function updated_user_date()
    {
        return $this->updated_user->name() . ", " . $this->updated_at->Format(trans('main.format_date')) . ", " . $this->updated_user->email;
    }

    function created_user_date_time()
    {
        return $this->created_user->name() . ", " . $this->created_at->Format(trans('main.format_date_time')) . ", " . $this->created_user->email;
    }

    function updated_user_date_time()
    {
        return $this->updated_user->name() . ", " . $this->updated_at->Format(trans('main.format_date_time')) . ", " . $this->updated_user->email;
    }

    function child_mains()
    {
        return $this->hasMany(Main::class, 'child_item_id');
    }

    function parent_mains()
    {
        return $this->hasMany(Main::class, 'parent_item_id');
    }

    public function text()
    {
        return $this->hasOne(Text::class);
    }

    // name() используется для отображения значений полей
    // $fullname = true/false - вывод полной строки (более 255 символов)
    // $numcat = true/false - вывод числовых полей с разрядом тысячи/миллионы/миллиарды
    // $rightnull = true/false - у вещественных чисел убрать правые нули после запятой
    function name_start($fullname = false, $numcat = false, $rightnull = false)
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
        $base = $this->base;
        if ($base) {
            if ($base->type_is_date()) {
                // "$this->name_lang_0 ??..." нужно, в случае если в $this->name_lang_0 хранится не дата
//               $result = $this->name_lang_0 ? date_create($this->name_lang_0)->Format(trans('main.format_date')):'';
                if (($timestamp = date_create($this->name_lang_0)) === false) {
                    $result = $this->name_lang_0;
                } else {
                    $result = date_create($this->name_lang_0)->Format(trans('main.format_date'));
                }
                // Не использовать
//                    $result = date_create($this->name_lang_0)->Format('Y.m.d');

            } elseif ($base->type_is_number()) {
                $result = GlobalController::restore_number_from_item($base, $this->name_lang_0, $numcat, $rightnull);

            } elseif ($base->type_is_boolean()) {
                //    Похожие строки в Base.php
                // #65794 - ранее был пустой квадратик
                $result = $this->name_lang_0 == "1" ? html_entity_decode('  &#9745;')
                    : ($this->name_lang_0 == "0" ? html_entity_decode('&#10065;') : trans('main.empty'));
                // Не использовать
//                $result = $this->name_lang_0 == "1" ? "1-".trans('main.true')
//                    : ($this->name_lang_0 == "0" ? "0-".trans('main.false') : trans('main.empty'));
                //

            } else {
                $index = array_search(App::getLocale(), config('app.locales'));
                if ($index !== false) {   // '!==' использовать, '!=' не использовать
                    $result = trim($this['name_lang_' . $index]);
                    if ($fullname == true) {
                        //ограниченные 255 - размером полей хранятся в $item->name_lang_0 - $item->name_lang_3
                        $maxlen = 255;
                        if (($base->is_calcname_lst == true) && (mb_strlen($result) >= $maxlen)) {
                            // похожи GlobalController::itnm_left() и Item.php ("...")
                            if (mb_substr($result, $maxlen - 3, 3) == "...") {
                                // Полное наименование, более 255 символов
                                //https://stackoverflow.com/questions/19693946/non-static-method-should-not-be-called-statically
                                $result = (new ItemController)->calc_value_func($this)['calc_full_lang_' . $index];
                            }
                        }
                    }
                }
            }
        }
//        if ($result == "") {
//            $result = $this->name_lang_0;
//        }

        return $result;
    }

    // "\~" - символ перевода каретки (используется также в Item.php: функции name() nmbr())
    // "\~" - символ перевода каретки (используется также в ItemController.php: функция calc_value_func())
    // $fullname = true/false - вывод полной строки (более 255 символов)
    // $numcat = true/false - вывод числовых полей с разрядом тысячи/миллионы/миллиарды
    // $rightnull = true/false - у вещественных чисел убрать правые нули после запятой
    function name($fullname = false, $numcat = false, $rightnull = false)
    {
        $result = self::name_start($fullname, $numcat, $rightnull);
        $result = str_replace('\~', '', $result);
        return $result;
    }

    // "\~" - символ перевода каретки (используется также в Item.php: функции name() nmbr())
    // "\~" - символ перевода каретки (используется также в ItemController.php: функция calc_value_func())
    // $numcat = true/false - вывод числовых полей с разрядом тысячи/миллионы/миллиарды
    function nmbr()
    {
        $result = self::name_start(true, false);
        $result = str_replace('\~', '<br>', $result);
        return $result;
    }

    //names() используется для расчета вычисляемого наименования
    function names()
    {
        $res_array = array();
        // массив "glo_menu_main" показывает, что четыре поля наименований хранятся в bases и items
        // ['1', '2', '3', '4'] - тут разницы нет, какие значения хранятся; главное, чтобы что-то хранилось
        $main_array = ['1', '2', '3', '4'];
        // Сохранить текущий язык
        $locale = App::getLocale();
//        foreach (session('glo_menu_main') as $lang_key => $lang_value) {
        foreach ($main_array as $lang_key => $lang_value) {
            $name = "";  // нужно, не удалять
            if ($lang_key < count(config('app.locales'))) {
                $lc = config('app.locales')[$lang_key];
                App::setLocale($lc);
                $base = $this->base;
                if ($base) {
                    // Эта строка нужна, не удалять
                    // Для полей типа текст ө наименование берется из $item->name_lang_x, а не с $text->name_lang_x
                    $name = $this['name_lang_' . $lang_key];
                    if ($base->type_is_date()) {
                        $name = date_create($name)->Format(trans('main.format_date'));
                        // Нужно для правильной сортировки по полю $item->name_lang_x
                        //$name = date_create($name)->Format('Y.m.d');

                    } elseif ($base->type_is_number()) {
                        $name = GlobalController::restore_number_from_item($base, $name);

                    } elseif ($base->type_is_boolean()) {
                        //    Похожие строки в Base.php
//                    $name = $name == "1" ? html_entity_decode('	&#9745;')
//                        : ($name == "0" ? html_entity_decode('&#10065;') : trans('main.empty'));
                        // Нужно для правильной сортировки по полю $item->name_lang_x
                        $name = $name == "1" ? trans('main.yes')
                            : ($name == "0" ? trans('main.no') : trans('main.empty'));
                        //
                    }
                }
            }
            $res_array[$lang_key] = $name;
        }
        // Восстановить текущий язык
        App::setLocale($locale);
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
            // Показывать для пользователя, создавшего фото
            // Для другого пользователя - проверка на модерацию
            $check = false;
            if (Auth::check()) {
                $check = $this->created_user_id != Auth::user()->id;
            } else {
                $check = true;
            }
            if ($check) {
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
                    // Показывать для пользователя, создавшего фото
                    if ($this->created_user_id == Auth::user()->id) {
                        if ($this->name_lang_2 != "") {
                            $result = $result . ": " . $this->name_lang_2;
                        }
                    }
                }
            }
        }
        return $result;
    }

// Возвращает true, если статус =  "не прошло модерацию"  и есть комментарий
//    function is_no_moderation_info()
//    {
//        $result = false;
//        if ($this->base->type_is_image() == true) {
//            // Показывать для пользователя, создавшего фото
//            if ($this->created_user_id == Auth::user()->id) {
//                if ($this->base->is_to_moderate_image == true) {
//                    // Не прошло модерацию
//                    if ($this->name_lang_1 == "2") {
//                        $result = trans('main.did_not_pass_the_moderation');
//                        if ($this->name_lang_2 != "") {
//                            $result = true;
//                        }
//                    }
//                }
//            }
//        }
//        return $result;
//    }

// Возвращает true, если статус =  "на модерации и не прошло модерацию"
// для пользователя, создавшего фото
    function is_moderation_info()
    {
        $result = false;
        if ($this->base->type_is_image() == true) {
            // Показывать для пользователя, создавшего фото
            $check = false;
            if (Auth::check()) {
                $check = $this->created_user_id == Auth::user()->id;
            } else {
                $check = false;
            }
            if ($check) {
                if ($this->base->is_to_moderate_image == true) {
                    // На модерации
                    if ($this->name_lang_1 == "3") {
                        $result = true;
                    }
                    // Не прошло модерацию
                    if ($this->name_lang_1 == "2") {
                        $result = true;
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

// Для типа полей Число
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
