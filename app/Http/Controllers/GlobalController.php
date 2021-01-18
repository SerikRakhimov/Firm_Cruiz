<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Models\Base;
use App\Models\Link;
use App\Models\Item;
use App\Models\Project;
use App\Models\Role;
use App\Models\Roba;
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

    static function glo_project_role_setnull()
    {
        Session::put('glo_project_id', 0);
        Session::put('glo_role_id', 0);
    }

    static function glo_project_id()
    {
        $result = 0;
        // если существует переменная в сессии
        if (Session::has('glo_project_id')) {
            $glo_project_id = session('glo_project_id');
            if ($glo_project_id != 0) {
                $result = Project::findOrFail($glo_project_id)->id;
            }
        }
        return $result;
    }

    static function glo_project_name()
    {
        $result = "";
        // если существует переменная в сессии
        if (Session::has('glo_project_id')) {
            $glo_project_id = session('glo_project_id');
            if ($glo_project_id != 0) {
                $result = Project::findOrFail($glo_project_id)->name();
            }
        }
        return $result;
    }

    static function glo_project_template_id()
    {
        $result = 0;
        // если существует переменная в сессии
        if (Session::has('glo_project_id')) {
            $glo_project_id = session('glo_project_id');
            if ($glo_project_id != 0) {
                $result = Project::findOrFail($glo_project_id)->template_id;
            }
        }
        return $result;
    }

    static function glo_role()
    {
        $result = null;
        // если существует переменная в сессии
        if (Session::has('glo_role_id')) {
            $glo_role_id = session('glo_role_id');
            if ($glo_role_id != 0) {
                $result = Role::findOrFail($glo_role_id);
            }
        }
        return $result;
    }

    static function glo_role_id()
    {
        $result = 0;
        // если существует переменная в сессии
        if (Session::has('glo_role_id')) {
            $glo_role_id = session('glo_role_id');
            if ($glo_role_id != 0) {
                $result = Role::findOrFail($glo_role_id)->id;
            }
        }
        return $result;
    }

    static function glo_role_name()
    {
        $result = "";
        // если существует переменная в сессии
        if (Session::has('glo_role_id')) {
            $glo_role_id = session('glo_role_id');
            if ($glo_role_id != 0) {
                $result = Role::findOrFail($glo_role_id)->name();
            }
        }
        return $result;
    }

    static function glo_project_role_is_null()
    {
        $glo_project_id = GlobalController::glo_project_id();
        $glo_role_id = GlobalController::glo_role_id();
        $result = $glo_project_id == 0 && $glo_role_id == 0;
        return $result;
    }

    static function name_is_boolean($value)
    {
        return $value == true ? html_entity_decode('	&#9745;')
            : ($value == false ? html_entity_decode('&#65794;') : trans('main.empty'));
    }

    static function base_right(Base $base, bool $is_no_sndb_rule = null)
    {
        $role = GlobalController::glo_role();

        $is_list_base_create = $role->is_list_base_create;
        $is_list_base_read = $role->is_list_base_read;
        $is_list_base_update = $role->is_list_base_update;
        $is_list_base_delete = $role->is_list_base_delete;
        $is_list_base_byuser = $role->is_list_base_byuser;
        $is_edit_base_read = $role->is_edit_base_read;
        $is_edit_base_update = $role->is_edit_base_update;
        $is_list_link_enable = $role->is_list_link_enable;
        $is_show_base_enable = $role->is_show_base_enable;
        $is_show_link_enable = $role->is_show_link_enable;
        $is_edit_link_read = $role->is_edit_link_read;
        $is_edit_link_update = $role->is_edit_link_update;

        // Блок проверки по Role
        // "$is_enable = true" нужно
        $is_list_base_enable = true;
        if (!$is_no_sndb_rule) {
            if ($role->is_list_base_sndb == false) {
                if ($base->type_is_number == true || $base->type_is_string == true ||
                    $base->type_is_date == true || $base->type_is_boolean == true) {
                    $is_list_base_enable = false;
                }
            }
        }

        if ($is_list_base_read == true) {
            $is_list_base_create = false;
            $is_list_base_update = false;
            $is_list_base_delete = false;
        }
        // "$is_enable &&" нужно
        $is_list_base_enable = $is_list_base_enable && ($is_list_base_create || $is_list_base_read || $is_list_base_update || $is_list_base_delete);
        $is_edit_base_enable = $is_edit_base_read || $is_edit_base_update;
        $is_edit_link_enable = $is_edit_link_read || $is_edit_link_update;

        // Блок проверки по Robas, используя переменные $role и $base

        $roba = Roba::where('role_id', $role->id)->where('base_id', $base->id)->first();
        if ($roba != null) {
            $is_roba_list_base_create = $roba->is_list_base_create;
            $is_roba_list_base_read = $roba->is_list_base_read;
            $is_roba_list_base_update = $roba->is_list_base_update;
            $is_roba_list_base_delete = $roba->is_list_base_delete;
            $is_roba_list_base_byuser = $roba->is_list_base_byuser;
            $is_roba_edit_base_read = $roba->is_edit_base_read;
            $is_roba_edit_base_update = $roba->is_edit_base_update;
            $is_roba_list_link_enable = $roba->is_list_link_enable;
            $is_roba_show_base_enable = $roba->is_show_base_enable;
            $is_roba_show_link_enable = $roba->is_show_link_enable;
            $is_roba_edit_link_read = $roba->is_edit_link_read;
            $is_roba_edit_link_update = $roba->is_edit_link_update;
            if ($is_roba_list_base_read == true) {
                $is_roba_list_base_create = false;
                $is_roba_list_base_update = false;
                $is_roba_list_base_delete = false;
            }

            $is_roba_list_base_enable = $is_roba_list_base_create || $is_roba_list_base_read || $is_roba_list_base_update || $is_roba_list_base_delete;
            $is_roba_edit_base_enable = $is_roba_edit_base_read || $is_roba_edit_base_update;
            $is_roba_edit_link_enable = $is_roba_edit_link_read || $is_roba_edit_link_update;

            $is_list_base_enable = $is_roba_list_base_enable;
            $is_list_base_create = $is_roba_list_base_create;
            $is_list_base_read = $is_roba_list_base_read;
            $is_list_base_update = $is_roba_list_base_update;
            $is_list_base_delete = $is_roba_list_base_delete;
            $is_list_base_byuser = $is_roba_list_base_byuser;
            $is_edit_base_enable = $is_roba_edit_base_enable;
            $is_edit_base_read = $is_roba_edit_base_read;
            $is_edit_base_update = $is_roba_edit_base_update;
            $is_list_link_enable = $is_roba_list_link_enable;
            $is_edit_link_enable = $is_roba_edit_link_enable;
            $is_show_base_enable = $is_roba_show_base_enable;
            $is_show_link_enable = $is_roba_show_link_enable;
            $is_edit_link_read = $is_roba_edit_link_read;
            $is_edit_link_update = $is_roba_edit_link_update;
        }

        return ['is_list_base_enable' => $is_list_base_enable,
            'is_list_base_create' => $is_list_base_create,
            'is_list_base_read' => $is_list_base_read,
            'is_list_base_update' => $is_list_base_update,
            'is_list_base_delete' => $is_list_base_delete,
            'is_list_base_byuser' => $is_list_base_byuser,
            'is_edit_base_enable' => $is_edit_base_enable,
            'is_edit_base_read' => $is_edit_base_read,
            'is_edit_base_update' => $is_edit_base_update,
            'is_list_link_enable' => $is_list_link_enable,
            'is_show_base_enable' => $is_show_base_enable,
            'is_show_link_enable' => $is_show_link_enable,
            'is_edit_link_enable' => $is_edit_link_enable,
            'is_edit_link_read' => $is_edit_link_read,
            'is_edit_link_update' => $is_edit_link_update
        ];
    }

    static function base_link_right(Link $link)
    {
        $base = $link->parent_base;
        $base_right = self::base_right($base, true);

        $is_list_base_enable = $base_right['is_list_base_enable'];
        $is_list_base_create = $base_right['is_list_base_create'];
        $is_list_base_read = $base_right['is_list_base_read'];
        $is_list_base_update = $base_right['is_list_base_update'];
        $is_list_base_delete = $base_right['is_list_base_delete'];
        $is_list_base_byuser = $base_right['is_list_base_byuser'];
        $is_edit_base_enable = $base_right['is_edit_base_enable'];
        $is_edit_base_read = $base_right['is_edit_base_read'];
        $is_edit_base_update = $base_right['is_edit_base_update'];
        $is_list_link_enable = $base_right['is_list_link_enable'];
        $is_show_base_enable = $base_right['is_show_base_enable'];
        $is_show_link_enable = $base_right['is_show_link_enable'];
        $is_edit_link_read = $base_right['is_edit_link_read'];
        $is_edit_link_update = $base_right['is_edit_link_update'];
        $is_edit_link_enable = $is_edit_link_read || $is_edit_link_update;

        return ['is_list_base_enable' => $is_list_base_enable,
            'is_list_base_create' => $is_list_base_create,
            'is_list_base_read' => $is_list_base_read,
            'is_list_base_update' => $is_list_base_update,
            'is_list_base_delete' => $is_list_base_delete,
            'is_list_base_byuser' => $is_list_base_byuser,
            'is_edit_base_enable' => $is_edit_base_enable,
            'is_edit_base_read' => $is_edit_base_read,
            'is_edit_base_update' => $is_edit_base_update,
            'is_list_link_enable' => $is_list_link_enable,
            'is_show_base_enable' => $is_show_base_enable,
            'is_show_link_enable' => $is_show_link_enable,
            'is_edit_link_enable' => $is_edit_link_enable,
            'is_edit_link_read' => $is_edit_link_read,
            'is_edit_link_update' => $is_edit_link_update,
        ];
    }

    static function items_right(Base $base)
    {
        $base_right = self::base_right($base);
        $items = Item::where('base_id', $base->id)->where('project_id', GlobalController::glo_project_id());
        if ($base_right['is_list_base_byuser'] == true) {
            $items = $items->where('updated_user_id', GlobalController::glo_user_id());
        }
        $name = "";  // нужно, не удалять
        $index = array_search(session('locale'), session('glo_menu_save'));
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
}
