<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Base;
use App\Models\Link;
use App\Models\Item;
use App\Models\Project;
use App\Models\Role;
use App\Models\Roba;
use App\Models\Roli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Boolean;

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

    static function name_is_boolean($value)
    {
        return $value == true ? html_entity_decode('	&#9745;')
            : ($value == false ? html_entity_decode('&#65794;') : trans('main.empty'));
    }

    static function base_right(Base $base, Role $role, bool $is_no_sndb_pd_rule = false)
    {
        $is_all_base_calcname_enable = $role->is_all_base_calcname_enable;
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
        // Блок проверки по Role
        // "$is_list_base_calc = true" нужно
        $is_list_base_calc = true;
        if (!$is_no_sndb_pd_rule) {
            if ($role->is_list_base_sndb == false) {
                if ($base->type_is_number == true || $base->type_is_string == true ||
                    $base->type_is_date == true || $base->type_is_boolean == true) {
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
        }

        $is_edit_base_enable = $is_edit_base_read || $is_edit_base_update;
        $is_edit_link_enable = $is_edit_link_read || $is_edit_link_update;
//
        return ['is_list_base_calc' => $is_list_base_calc,
            'is_all_base_calcname_enable' => $is_all_base_calcname_enable,
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
            'is_edit_link_update' => $is_edit_link_update
        ];
    }

    static function base_link_right(Link $link, Role $role)
    {
        $base = $link->parent_base;
        $base_right = self::base_right($base, $role, true);

        $is_list_base_calc = $base_right['is_list_base_calc'];
        $is_all_base_calcname_enable = $base_right['is_all_base_calcname_enable'];
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
        ];
    }

    static function items_right(Base $base, Project $project, Role $role)
    {
        $base_right = self::base_right($base, $role);
        $items = Item::where('base_id', $base->id)->where('project_id', $project->id);
        // Такая же проверка и в ItemController (function browser(), get_items_for_link())
        if ($base_right['is_list_base_byuser'] == true) {
            $items = $items->where('created_user_id', GlobalController::glo_user_id());
        }
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


        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $items = $items->orderBy($name);
        }

        $itget = $items->get();
        return ['items' => $items, 'itget' => $itget];
    }

    static function empty_html()
    {
        return trans('main.empty');
    }

    static function image_is_missing_html()
    {
        return trans('main.image_is_missing');
    }

//  Если тип-вычисляемое поле и показывать вычисляемое поле
    static function is_base_calcname_enable($base, $base_right)
    {
        return ($base->is_calcname_lst == true && $base_right['is_all_base_calcname_enable'] == true)
            || ($base->is_calcname_lst == false);
    }

}
