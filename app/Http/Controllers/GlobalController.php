<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GlobalController extends Controller
{
    static function start_artisan(){
        //Artisan::call('migrate');
        Artisan::call('migrate', ['--path' => 'vendor/systeminc/laravel-admin/src/database/migrations']);
        //Artisan::call('migrate:refresh', ['--path' => 'database/migrations']);
        // для настройки папки storage
        Artisan::call('storage:link');
    }
}
