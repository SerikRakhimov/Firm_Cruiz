<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Models\Base;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

    static function base_right(Role $role, Base $base)
    {
        $is_enable = false;
        $is_create = false;
        $is_read = false;
        $is_update = false;
        $is_delete = false;

        $is_enable = true;
        if (!$role->is_sndb == true) {
            if ($base->type_is_number == true || $base->type_is_string == true ||
                $base->type_is_date == true || $base->type_is_boolean == true) {
                $is_enable = false;
            }
        }

        $is_create = false;
        $is_read = false;
        $is_update = false;
        $is_delete = false;
        if (!$role->is_create == true) {
            $is_create = true;
        }
        if (!$role->is_read == true) {
            $is_read = true;
        }
        if (!$role->is_update == true) {
            $is_update = true;
        }
        if (!$role->is_delete == true) {
            $is_delete = true;
        }
        if ($is_read == true) {
            $is_create = false;
            $is_update = false;
            $is_delete = false;
        }
        return ['is_enable' => $is_enable, 'is_create' => $is_create, 'is_read' => $is_read, 'is_update' => $is_update, 'is_delete' => $is_delete];
    }

}
