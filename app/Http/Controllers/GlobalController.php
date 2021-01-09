<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class GlobalController extends Controller
{
    static function start_artisan(){
        Artisan::call('migrate');
        //Artisan::call('migrate', ['--path' => 'vendor/systeminc/laravel-admin/src/database/migrations']);
        //Artisan::call('migrate:refresh', ['--path' => 'database/migrations']);
        // для настройки папки storage
        Artisan::call('storage:link');
    }

    static function name_is_boolean($value)
    {
        return $value == true ? html_entity_decode('	&#9745;')
            : ($value == false ? html_entity_decode('&#65794;') : trans('main.empty'));
    }

}
