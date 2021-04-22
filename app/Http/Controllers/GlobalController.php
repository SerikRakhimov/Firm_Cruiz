<?php

namespace App\Http\Controllers;

use App\Models\Set;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Base;
use App\Models\Link;
use App\Models\Item;
use App\Models\Main;
use App\Models\Text;
use App\Models\Project;
use App\Models\Role;
use App\Models\Roba;
use App\Models\Roli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Collection;

class GlobalController extends Controller
{
    static function start_artisan()
    {
        Artisan::call('migrate');
        //Artisan::call('migrate', ['--path' => 'vendor/systeminc/laravel-admin/src/database/migrations']);
        //Artisan::call('migrate:refresh', ['--path' => 'database/migrations']);
        // для настройки папки storage
        Artisan::call('storage:link');
    }

    static function glo_user()
    {
        return Auth::user();
    }

    static function glo_user_id()
    {
        return Auth::user()->id;
    }

//    Похожие строки в Item.php
    static function deleted_user_date()
    {
        return self::glo_user()->name() . ", " . date(trans('main.format_date')) . ", " . self::glo_user()->email;
    }

    static function deleted_user_date_time()
    {
        return self::glo_user()->name() . ", " . date(trans('main.format_date_time')) . ", " . self::glo_user()->email;
    }

    static function name_is_boolean($value)
    {
        return $value == true ? html_entity_decode('	&#9745;')
            : ($value == false ? html_entity_decode('&#10065;') : trans('main.empty'));
    }

    static function base_right(Base $base, Role $role, bool $is_no_sndb_pd_rule = false)
    {
        $is_all_base_calcname_enable = $role->is_all_base_calcname_enable;
        $is_list_base_sort_creation_date_desc = $role->is_list_base_sort_creation_date_desc;
        $is_list_base_create = $role->is_list_base_create;
        $is_list_base_read = $role->is_list_base_read;
        $is_list_base_update = $role->is_list_base_update;
        $is_list_base_delete = $role->is_list_base_delete;
        $is_list_base_used_delete = $role->is_list_base_used_delete;
        $is_list_base_byuser = $role->is_list_base_byuser;
        $is_edit_base_read = $role->is_edit_base_read;
        $is_edit_base_update = $role->is_edit_base_update;
        $is_list_base_enable = $role->is_list_base_enable;
        $is_list_link_enable = $role->is_list_link_enable;
        $is_show_base_enable = $role->is_show_base_enable;
        $is_show_link_enable = $role->is_show_link_enable;
        $is_edit_link_read = $role->is_edit_link_read;
        $is_edit_link_update = $role->is_edit_link_update;
        $is_edit_email_base_create = $role->is_edit_email_base_create;
        $is_edit_email_question_base_create = $role->is_edit_email_question_base_create;
        $is_edit_email_base_update = $role->is_edit_email_base_update;
        $is_edit_email_question_base_update = $role->is_edit_email_question_base_update;
        $is_show_email_base_delete = $role->is_show_email_base_delete;
        $is_show_email_question_base_delete = $role->is_show_email_question_base_delete;
        // Блок проверки по Role
        // "$is_list_base_calc = true" нужно
        $is_list_base_calc = true;
        if (!$is_no_sndb_pd_rule) {
            if ($role->is_list_base_sndbt == false) {
                if ($base->type_is_number() || $base->type_is_string() ||
                    $base->type_is_date() || $base->type_is_boolean() || $base->type_is_text()) {
                    $is_list_base_calc = false;
                }
            }
            if ($role->is_list_base_id == false) {
                if ($base->type_is_image == true || $base->type_is_document == true) {
                    $is_list_base_calc = false;
                }
            }
        }

//        if ($is_list_base_read == true) {
//            $is_list_base_create = false;
//            $is_list_base_update = false;
//            $is_list_base_delete = false;
//        }
        // "$is_enable &&" нужно
        $is_list_base_calc = $is_list_base_calc && ($is_list_base_create || $is_list_base_read || $is_list_base_update || $is_list_base_delete);

        // Блок проверки по robas, используя переменные $role и $base
        $roba = Roba::where('role_id', $role->id)->where('base_id', $base->id)->first();
        if ($roba != null) {
            $is_roba_all_base_calcname_enable = $roba->is_all_base_calcname_enable;
            $is_roba_list_base_sort_creation_date_desc = $roba->is_list_base_sort_creation_date_desc;
            $is_roba_list_base_create = $roba->is_list_base_create;
            $is_roba_list_base_read = $roba->is_list_base_read;
            $is_roba_list_base_update = $roba->is_list_base_update;
            $is_roba_list_base_delete = $roba->is_list_base_delete;
            $is_roba_list_base_used_delete = $roba->is_list_base_used_delete;
            $is_roba_list_base_byuser = $roba->is_list_base_byuser;
            $is_roba_edit_base_read = $roba->is_edit_base_read;
            $is_roba_edit_base_update = $roba->is_edit_base_update;
            $is_roba_list_base_enable = $roba->is_list_base_enable;
            $is_roba_list_link_enable = $roba->is_list_link_enable;
            $is_roba_show_base_enable = $roba->is_show_base_enable;
            $is_roba_show_link_enable = $roba->is_show_link_enable;
            $is_roba_edit_link_read = $roba->is_edit_link_read;
            $is_roba_edit_link_update = $roba->is_edit_link_update;
            $is_roba_edit_email_base_create = $roba->is_edit_email_base_create;
            $is_roba_edit_email_question_base_create = $roba->is_edit_email_question_base_create;
            $is_roba_edit_email_base_update = $roba->is_edit_email_base_update;
            $is_roba_edit_email_question_base_update = $roba->is_edit_email_question_base_update;
            $is_roba_show_email_base_delete = $roba->is_show_email_base_delete;
            $is_roba_show_email_question_base_delete = $roba->is_show_email_question_base_delete;

//            if ($is_roba_list_base_read == true) {
//                $is_roba_list_base_create = false;
//                $is_roba_list_base_update = false;
//                $is_roba_list_base_delete = false;
//            }

            $is_roba_list_base_calc = $is_roba_list_base_create || $is_roba_list_base_read || $is_roba_list_base_update || $is_roba_list_base_delete;
//            $is_roba_edit_base_enable = $is_roba_edit_base_read || $is_roba_edit_base_update;
//            $is_roba_edit_link_enable = $is_roba_edit_link_read || $is_roba_edit_link_update;

            $is_list_base_calc = $is_roba_list_base_calc;
            $is_all_base_calcname_enable = $is_roba_all_base_calcname_enable;
            $is_list_base_sort_creation_date_desc = $is_roba_list_base_sort_creation_date_desc;
            $is_list_base_create = $is_roba_list_base_create;
            $is_list_base_read = $is_roba_list_base_read;
            $is_list_base_update = $is_roba_list_base_update;
            $is_list_base_delete = $is_roba_list_base_delete;
            $is_list_base_used_delete = $is_roba_list_base_used_delete;
            $is_list_base_byuser = $is_roba_list_base_byuser;
//            $is_edit_base_enable = $is_roba_edit_base_enable;
            $is_edit_base_read = $is_roba_edit_base_read;
            $is_edit_base_update = $is_roba_edit_base_update;
            $is_list_base_enable = $is_roba_list_base_enable;
            $is_list_link_enable = $is_roba_list_link_enable;
//            $is_edit_link_enable = $is_roba_edit_link_enable;
            $is_show_base_enable = $is_roba_show_base_enable;
            $is_show_link_enable = $is_roba_show_link_enable;
            $is_edit_link_read = $is_roba_edit_link_read;
            $is_edit_link_update = $is_roba_edit_link_update;
            $is_edit_email_base_create = $is_roba_edit_email_base_create;
            $is_edit_email_question_base_create = $is_roba_edit_email_question_base_create;
            $is_edit_email_base_update = $is_roba_edit_email_base_update;
            $is_edit_email_question_base_update = $is_roba_edit_email_question_base_update;
            $is_show_email_base_delete = $is_roba_show_email_base_delete;
            $is_show_email_question_base_delete = $is_roba_show_email_question_base_delete;
        }

        $is_edit_base_enable = $is_edit_base_read || $is_edit_base_update;
        $is_edit_link_enable = $is_edit_link_read || $is_edit_link_update;
//
        return ['is_list_base_calc' => $is_list_base_calc,
            'is_all_base_calcname_enable' => $is_all_base_calcname_enable,
            'is_list_base_sort_creation_date_desc' => $is_list_base_sort_creation_date_desc,
            'is_list_base_create' => $is_list_base_create,
            'is_list_base_read' => $is_list_base_read,
            'is_list_base_update' => $is_list_base_update,
            'is_list_base_delete' => $is_list_base_delete,
            'is_list_base_used_delete' => $is_list_base_used_delete,
            'is_list_base_byuser' => $is_list_base_byuser,
            'is_edit_base_enable' => $is_edit_base_enable,
            'is_edit_base_read' => $is_edit_base_read,
            'is_edit_base_update' => $is_edit_base_update,
            'is_list_base_enable' => $is_list_base_enable,
            'is_list_link_enable' => $is_list_link_enable,
            'is_show_base_enable' => $is_show_base_enable,
            'is_show_link_enable' => $is_show_link_enable,
            'is_edit_link_enable' => $is_edit_link_enable,
            'is_edit_link_read' => $is_edit_link_read,
            'is_edit_link_update' => $is_edit_link_update,
            'is_edit_email_base_create' => $is_edit_email_base_create,
            'is_edit_email_question_base_create' => $is_edit_email_question_base_create,
            'is_edit_email_base_update' => $is_edit_email_base_update,
            'is_edit_email_question_base_update' => $is_edit_email_question_base_update,
            'is_show_email_base_delete' => $is_show_email_base_delete,
            'is_show_email_question_base_delete' => $is_show_email_question_base_delete
        ];
    }

    static function base_link_right(Link $link, Role $role)
    {
        $base = $link->parent_base;
        $base_right = self::base_right($base, $role, true);

        $is_list_base_calc = $base_right['is_list_base_calc'];
        $is_all_base_calcname_enable = $base_right['is_all_base_calcname_enable'];
        $is_list_base_sort_creation_date_desc = $base_right['is_list_base_sort_creation_date_desc'];
        $is_list_base_create = $base_right['is_list_base_create'];
        $is_list_base_read = $base_right['is_list_base_read'];
        $is_list_base_update = $base_right['is_list_base_update'];
        $is_list_base_delete = $base_right['is_list_base_delete'];
        $is_list_base_used_delete = $base_right['is_list_base_used_delete'];
        $is_list_base_byuser = $base_right['is_list_base_byuser'];
        $is_edit_base_enable = $base_right['is_edit_base_enable'];
        $is_edit_base_read = $base_right['is_edit_base_read'];
        $is_edit_base_update = $base_right['is_edit_base_update'];
        $is_list_base_enable = $base_right['is_list_base_enable'];
        $is_list_link_enable = $base_right['is_list_link_enable'];
        $is_show_base_enable = $base_right['is_show_base_enable'];
        $is_show_link_enable = $base_right['is_show_link_enable'];
        $is_edit_link_read = $base_right['is_edit_link_read'];
        $is_edit_link_update = $base_right['is_edit_link_update'];
        $is_edit_email_base_create = $base_right['is_edit_email_base_create'];
        $is_edit_email_question_base_create = $base_right['is_edit_email_question_base_create'];
        $is_edit_email_base_update = $base_right['is_edit_email_base_update'];
        $is_edit_email_question_base_update = $base_right['is_edit_email_question_base_update'];
        $is_show_email_base_delete = $base_right['is_show_email_base_delete'];
        $is_show_email_question_base_delete = $base_right['is_show_email_question_base_delete'];
        //  Проверка скрывать поле или нет
        if ($link->parent_is_hidden_field == true) {
            $is_list_link_enable = false;
            $is_show_link_enable = false;
            $is_edit_link_read = false;
            // При корректировке в форме ставится пометка hidden
            //$is_edit_link_update = false;
        }
        // Блок проверки по rolis, используя переменные $role и $link
        $roli = Roli::where('role_id', $role->id)->where('link_id', $link->id)->first();
        if ($roli != null) {
            $is_list_link_enable = $roli->is_list_link_enable;
            $is_show_link_enable = $roli->is_show_link_enable;
            $is_edit_link_read = $roli->is_edit_link_read;
            $is_edit_link_update = $roli->is_edit_link_update;
        }
        $is_edit_link_enable = $is_edit_link_read || $is_edit_link_update;

        return ['is_list_base_calc' => $is_list_base_calc,
            'is_all_base_calcname_enable' => $is_all_base_calcname_enable,
            'is_list_base_sort_creation_date_desc' => $is_list_base_sort_creation_date_desc,
            'is_list_base_create' => $is_list_base_create,
            'is_list_base_read' => $is_list_base_read,
            'is_list_base_update' => $is_list_base_update,
            'is_list_base_delete' => $is_list_base_delete,
            'is_list_base_used_delete' => $is_list_base_used_delete,
            'is_list_base_byuser' => $is_list_base_byuser,
            'is_edit_base_enable' => $is_edit_base_enable,
            'is_edit_base_read' => $is_edit_base_read,
            'is_edit_base_update' => $is_edit_base_update,
            'is_list_base_enable' => $is_list_base_enable,
            'is_list_link_enable' => $is_list_link_enable,
            'is_show_base_enable' => $is_show_base_enable,
            'is_show_link_enable' => $is_show_link_enable,
            'is_edit_link_enable' => $is_edit_link_enable,
            'is_edit_link_read' => $is_edit_link_read,
            'is_edit_link_update' => $is_edit_link_update,
            'is_edit_email_base_create' => $is_edit_email_base_create,
            'is_edit_email_question_base_create' => $is_edit_email_question_base_create,
            'is_edit_email_base_update' => $is_edit_email_base_update,
            'is_edit_email_question_base_update' => $is_edit_email_question_base_update,
            'is_show_email_base_delete' => $is_show_email_base_delete,
            'is_show_email_question_base_delete' => $is_show_email_question_base_delete
        ];
    }

    // Не удалять
//
//        $items = $items->whereHas('child_mains', function ($query) {
//            $query->where('parent_item_id', 358);
//        });
//

//        $items = $items->whereHas('child_mains', function ($query) {
//            $query->where('link_id', 11)->where('parent_item_id', 152);
//        });
//        $items = $items->whereHas('child_mains', function ($query) {
//            $query->where('link_id', 11)->whereHas('parent_item', function ($query) {
//                $query->where(strval('name_lang_0'), '<=',500);});
//        });


//        $items = $items->whereHas('child_mains', function ($query) {
//            $query->where('link_id', 11)->whereHas('parent_item', function ($query) {
//                $query->where(strval('name_lang_0'), '<=',500);});
//        })->whereHas('child_mains', function ($query) {
//            $query->where('link_id', 3)->whereHas('parent_item', function ($query) {
//                $query->whereDate('name_lang_0', '>','2020-02-09');});
//        });

    static function items_right(Base $base, Project $project, Role $role)
    {

        $base_right = self::base_right($base, $role);

        // Обязательно фильтр на два запроса:
        // where('base_id', $base->id)->where('project_id', $project->id)
        $items = Item::where('base_id', $base->id)->where('project_id', $project->id);

        // Сортировать по дате создания записи в порядке убывания
        if ($base_right['is_list_base_sort_creation_date_desc'] == true) {
            //$items = $items->orderByDesc('created_user_id');
            $items = $items->latest();
        } else {
            $name = "";  // нужно, не удалять
            $index = array_search(App::getLocale(), config('app.locales'));
            if ($index !== false) {   // '!==' использовать, '!=' не использовать
                $name = 'name_lang_' . $index;
            }

            // В $collection сохраняется в key - $item->id
            $collection = collect();
            $items = $items->orderBy($name);

            //if (count($items->get()) > 0) {
            // Такая же проверка и в ItemController (function browser(), get_items_for_link())
            if ($base_right['is_list_base_byuser'] == true) {
                if (Auth::check()) {
                    $items = $items->where('created_user_id', GlobalController::glo_user_id());
                } else {
                    $items = null;
                    $collection = null;
                }
            }

            if ($items != null) {
                // Эта проверка нужна "if (count($items->get()) > 0)", иначе ошибка SQL
                if (count($items->get()) > 0) {
                    // Сортировка по mains
                    // иначе Сортировка по наименованию
                    if (!GlobalController::is_base_calcname_check($base, $base_right)) {

                        // Не попадают в список $mains изображения/документы,
                        // а также связанные поля (они в Mains не хранятся)
//            $mains = Main::select(DB::Raw('mains.child_item_id as item_id'))
//                ->join('links as ln', 'mains.link_id', '=', 'ln.id')
//                ->join('items as ct', 'mains.child_item_id', '=', 'ct.id')
//                ->join('bases as bs', 'ct.base_id', '=', 'bs.id')
//                ->where('ct.base_id', '=', $base->id)
//                ->where('ct.project_id', '=', $project->id)
//                ->where('bs.type_is_image', false)
//                ->where('bs.type_is_document', false)
//                ->orderBy('ln.parent_base_number')
//                ->orderBy('ct.' . $name)
//                ->distinct();

                        // Не попадают в список $links изображения/документы,
                        $links = Link::select(DB::Raw('links.*'))
                            ->join('bases as pb', 'links.parent_base_id', '=', 'pb.id')
                            ->where('links.child_base_id', '=', $base->id)
                            ->where('pb.type_is_image', false)
                            ->where('pb.type_is_document', false)
                            ->orderBy('links.parent_base_number')->get();

                        $items = $items->get();
                        $str = "";
                        foreach ($items as $item) {
                            $str = "";
                            foreach ($links as $link) {
                                $item_find = MainController::view_info($item->id, $link->id);
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

//              Не удалять
//            $mains = Main::select(DB::Raw('mains.child_item_id as item_id'))
//                ->join('links as ln', 'mains.link_id', '=', 'ln.id')
//                ->join('items as ct', 'mains.child_item_id', '=', 'ct.id')
//                ->join('items as pt', 'mains.parent_item_id', '=', 'pt.id')
//                ->join('bases as bs', 'pt.base_id', '=', 'bs.id')
//                ->where('ct.base_id', '=', $base->id)
//                ->where('ct.project_id', '=', $project->id)
//                ->where('bs.type_is_image', false)
//                ->where('bs.type_is_document', false)
//                ->orderBy('pt.base_id')
//                ->orderBy('pt.name_lang_0')
//                ->distinct();
////
//            $items = Item::joinSub($mains, 'mains', function ($join) {
//                $join->on('items.id', '=', 'mains.item_id');
//            });
                        $ids = $collection->keys()->toArray();
                        $items = Item::whereIn('id', $ids)
                            ->orderBy(\DB::raw("FIELD(id, " . implode(',', $ids) . ")"));
                    }
                }
            }
            //}
        }
        $itget = null;
        if ($items != null) {
            $itget = $items->get();
            $view_count = count($itget);
        } else {
            $itget = null;
            $view_count = mb_strtolower(trans('main.no_access'));
        }

        return ['items' => $items, 'itget' => $itget, 'view_count' => '(' . $view_count . ')'];
    }

    static function empty_html()
    {
        return trans('main.empty');
    }

    static function image_is_missing_html()
    {
        //        Изображение отсутствует
        return trans('main.image_is_missing');
    }

//  Если тип-вычисляемое поле(Вычисляемое наименование) и Показывать Основу с вычисляемым наименованием
//  или если тип-не вычисляемое наименование(Вычисляемое наименование)
    static function is_base_calcname_check($base, $base_right)
    {
//        $var = ($base->is_calcname_lst == true && $base_right['is_all_base_calcname_enable'] == true)
//            || ($base->is_calcname_lst == false);
//        echo "is_calcname_lst = " . $base->is_calcname_lst;
//        echo ", is_all_base_calcname_enable = " . $base_right['is_all_base_calcname_enable'];
//        echo ", result = " . $var;
        return ($base->is_calcname_lst == true && $base_right['is_all_base_calcname_enable'] == true)
            || ($base->is_calcname_lst == false);
    }

    static function check_project_user(Project $project, Role $role)
    {
        $result = false;
        if ($project->template_id == $role->template_id) {
            if ($role->is_default_for_external == true) {
                $result = true;
            } else {
                if ($role->is_author == true) {
                    if (Auth::check()) {
                        $result = $project->user_id == GlobalController::glo_user_id();
                    } else {
                        $result = false;
                    }
                }
            }
        } else {
            $result = false;
        }
        return $result;
    }

//    static function to_html($item)
//    {
//        $str = trim($item->base->sepa_calcname);
//        return str_replace($str, $str . '<br>', $item->name());
//    }

// На вход число в виде строки
// На выходе это же число с нулями спереди
// Нужно для правильной сортировки чисел
    static function save_number_to_item(Base $base, $str)
    {
        // Максимальное количество разрядов для числа
        $max_len = 17;
        $work_len = 0;
        $result = "";
        $str = trim($str);
        $first_char = "";
        $sminus = "-";
        // Первый символ равен "-"
        if (substr($str, 0, 1) == $sminus) {
            // Первый символ убирается
            $str = substr($str, 1);
            $work_len = $max_len - 1;
            $first_char = $sminus;
        } else {
            $work_len = $max_len;
            $first_char = "";
        }
        if ($base->type_is_number()) {
            $digits_num = $base->digits_num;

            // Число целое
            if ($digits_num == 0) {
                $int_value = intval($str);
                $result = $first_char . str_pad($int_value, $work_len, "0", STR_PAD_LEFT);

                // Число вещественное
            } else {
                $float_value = floatval($str);
                $float_value = sprintf("%1." . $digits_num . "f", floatval($float_value));
                $result = $first_char . str_pad($float_value, $work_len, "0", STR_PAD_LEFT);
            }
        }
        return $result;
    }

// На вход число с нулями спереди
// На выходе это же число в виде строки
// Нужно для правильного отображения чисел
// $numcat = true/false - вывод числовых полей с разрядом тысячи/миллионы/миллиарды
    static function restore_number_from_item(Base $base, $str, $numcat = false)
    {
        // Максимальное количество разрядов для числа
        $result = "";
        $str = trim($str);
        $first_char = "";
        $sminus = "-";
        // Первый символ равен "-"
        if (substr($str, 0, 1) == $sminus) {
            // Первый символ убирается
            $str = substr($str, 1);
            $first_char = $sminus;
        } else {
            $first_char = "";
        }
        if ($base->type_is_number()) {
            $digits_num = $base->digits_num;

            // Число целое
            if ($digits_num == 0) {
                $int_value = intval($str);
                // $numcat = true/false - вывод числовых полей с разрядом тысячи/миллионы/миллиарды
                if ($numcat) {
                    $result = $first_char . number_format($int_value, $digits_num, '.', ' ');
                } else {
                    $result = $first_char . strval($int_value);
                }

                // Число вещественное
            } else {
                $float_value = floatval($str);
                // $numcat = true/false - вывод числовых полей с разрядом тысячи/миллионы/миллиарды
                if ($numcat) {
                    $result = $first_char . number_format($float_value, $digits_num, '.', ' ');
                } else {
                    $result = $first_char . sprintf("%1." . $digits_num . "f", floatval($float_value));
                }
            }
        }
        return $result;
    }

    // Возвращает первые 255 символов переданной строки
    static function itnm_left($str)
    {
        // Убрать HTML-теги
        $str = strip_tags($str);
        // Нужно - убрать символы перевода строки (https://php.ru/forum/threads/udalenie-simvolov-perevoda-stroki.25065/)
        $str = str_replace(array("\r\n", "\r", "\n"), '', $str);
        //ограниченные 255 - размером полей хранятся в $item->name_lang_0 - $item->name_lang_3
        $maxlen = 255;
        $result = "";
        // похожи GlobalController::itnm_left() и Item.php ("...")
        if (strlen($str) > $maxlen) {
            //$result = mb_substr($str, 0, $maxlen - 3) . "...";
            $result = mb_substr($str, 0, $maxlen - 3) . strlen($str);
        } else {
            $result = mb_substr($str, 0, $maxlen);
        }
        return $result;
    }

    static function it_text_name(Item $item)
    {
        $result = "";
        //$text = $item->text();
        $text = Text::where('item_id', $item->id)->first();
        if ($text) {
            $result = $text->name();
        }
        return $result;
    }


    static function it_txnm_n2b(Item $item)
    {
        $result = nl2br(self::it_text_name($item));
        return $result;
    }

    // Проверяет текст на запрещенные html-теги
    static function text_html_check($text)
    {
        // Пробелы нужны "< html" и т.д.
        $array = array(
//            "< html",
            "<html","</html",
            "<head","</head",
            "<body","</body",
            "<script","</script",
            "<applet","</applet",
            "<form","</form",
            "<input","</input",
            "<button","</button",
            "<audio","</audio",
            "<img","</img",
            "<video","</video",
            "<a","</a",
            "onblur",
            "onchange",
            "onclick",
            "ondblclick",
            "onfocus",
            "onkeydown",
            "onkeypress",
            "onkeyup",
            "onload",
            "onmousedown",
            "onmousemove",
            "onmouseout",
            "onmouseover",
            "onmouseup",
            "onreset",
            "onselect",
            "onsubmit",
            "onunload"
        );
        $result = false;
        $message = "";
        if ($text == "" || $text == null) {
            $result = false;
        } else {
            foreach ($array as $value) {
                // Для поиска используется без пробела, например "<html" stripos
                //     if (mb_strpos(mb_strtolower($text), str_replace(" ", "", $value)) === false) {
                // Поиск без учета регистра с помощью функции stripos
                // Нужно так проверять "=== false" (https://fb.ru/article/375154/funktsiya-strpos-v-php-opredelenie-pozitsii-podstroki)
                //if (mb_stripos($text, str_replace(" ", "", $value)) === false) {
                if (mb_stripos($text, $value) === false) {
                } else {
                    $result = true;
                    // В переменную message присваивается с пробелом, чтобы при выводе echo $message эти теги не срабатывали
                    $message = trans('main.text_must_not_contain') . " '" . $value . "'";
                    break;
                }
            }
        }
        return ['result' => $result, 'message' => $message];
    }

}
