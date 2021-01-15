<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Models\Base;
use App\Models\Project;
use App\Models\Role;
use App\Models\Roba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

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

    static function base_right(Role $role, Base $base)
    {
        $is_enable = false;
        $is_create = false;
        $is_read = false;
        $is_update = false;
        $is_delete = false;
        $is_byuser = false;

        // Блок проверки по Role
        $is_enable = true;
        if ($role->is_sndb == false) {
            if ($base->type_is_number == true || $base->type_is_string == true ||
                $base->type_is_date == true || $base->type_is_boolean == true) {
                $is_enable = false;
            }
        }

        if ($role->is_create == true) {
            $is_create = true;
        }
        if ($role->is_read == true) {
            $is_read = true;
        }
        if ($role->is_update == true) {
            $is_update = true;
        }
        if ($role->is_delete == true) {
            $is_delete = true;
        }

        if ($is_read == true) {
            $is_create = false;
            $is_update = false;
            $is_delete = false;
        }
        // "$is_enable &&" нужно
        $is_enable = $is_enable && ($is_create || $is_read || $is_update || $is_delete);

        // Блок проверки по Robas, используя переменные $role и $base
        $is_roba_enable = false;
        $is_roba_create = false;
        $is_roba_read = false;
        $is_roba_update = false;
        $is_roba_delete = false;
        $roba = Roba::where('role_id', $role->id)->where('base_id', $base->id)->first();
        if ($roba != null) {
            if ($roba->is_create == true) {
                $is_roba_create = true;
            }
            if ($roba->is_read == true) {
                $is_roba_read = true;
            }
            if ($roba->is_update == true) {
                $is_roba_update = true;
            }
            if ($roba->is_delete == true) {
                $is_roba_delete = true;
            }

            if ($is_roba_read == true) {
                $is_roba_create = false;
                $is_roba_update = false;
                $is_roba_delete = false;
            }

            $is_roba_enable = $is_roba_create || $is_roba_read || $is_roba_update || $is_roba_delete;

//            $is_enable = $is_enable && $is_roba_enable;
//            $is_create = $is_create && $is_roba_create;
//            $is_read = $is_read && $is_roba_read;
//            $is_update = $is_update && $is_roba_update;
//            $is_delete = $is_delete && $is_roba_delete;

            $is_enable = $is_roba_enable;
            $is_create = $is_roba_create;
            $is_read = $is_roba_read;
            $is_update = $is_roba_update;
            $is_delete = $is_roba_delete;

            $is_byuser = $roba->is_byuser;

        }

        return ['is_enable' => $is_enable, 'is_create' => $is_create, 'is_read' => $is_read, 'is_update' => $is_update, 'is_delete' => $is_delete, 'is_byuser' => $is_byuser];
    }

}
