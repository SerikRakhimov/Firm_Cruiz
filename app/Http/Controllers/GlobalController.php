<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
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

    static function name_is_boolean($value)
    {
        return $value == true ? html_entity_decode('	&#9745;')
            : ($value == false ? html_entity_decode('&#65794;') : trans('main.empty'));
    }

}
