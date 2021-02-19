<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Artisan;
use App\Http\Controllers;
use Session;
use Hash;
use Auth;
use Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Session::regenerate();

//https://laravel.su/docs/5.0/validation
//
//public function authorize()
//{
//    $commentId = $this->route('comment');
//
//    return Comment::where('id', $commentId)
//        ->where('user_id', Auth::id())->exists();
//}

Route::get('/', function () {
    GlobalController::start_artisan();
    // массив "glo_menu_main" показывает, что четыре поля наименований хранятся в bases и items
    // ['1', '2', '3', '4'] - тут разницы нет, какие значения хранятся; главное, чтобы что-то хранилось
    $array = ['1', '2', '3', '4'];
    Session::put('glo_menu_main', $array);

    // массив "glo_menu_lang" показывает какие языки используются в меню
    // должно входить(не превышать) во множество 'locales'(config\app.php)
    $array = ['ru', 'kz', 'en'];
    Session::put('glo_menu_lang', $array);

    // массив "glo_menu_save" показывает, какие языки хранятся в bases и items
    // должно входить(не превышать) во множество массива 'glo_menu_lang'
    $array = ['ru', 'kz', 'en'];
    Session::put('glo_menu_save', $array);

    // текущий язык программы
    // должен совпадать с аналогичным значением в config\app.php
    Session::put('locale', 'ru');

    $user = \App\User::on()->first();
    if (!$user) {
        // создать новую запись для админа, если таблица users пуста
        $user = new \App\User();
        $user->name = 'admin';
        $user->email = 'admin@online.kz';
        $user->password = Hash::make('admin');
        $user->is_admin = true;
        $user->is_moderator = true;
        $user->save();
    }

    GlobalController::glo_project_role_setnull();
    	Mail::send(['html'=>'mail\mail_run'], ['remote_addr'=>$_SERVER['REMOTE_ADDR'], 'http_user_agent'=>$_SERVER['HTTP_USER_AGENT']], function($message){
    		$message->to('s_astana@mail.ru','')->subject('Игра запущена');
    		$message->from('support@rsb0807.kz','Игра Задумай-угадаю');
    	});

    if (Auth::check()) {
        //return view('welcome');
        return view('home');
        //return redirect()->route('order.index_job_user');
    } else {
        return redirect()->route('login');
    }

})
    ->name('/');

Route::get('/setlocale/{locale}', function ($locale) {

//    if (in_array($locale, \Config::get('app.locales'))) {   # Проверяем, что у пользователя выбран доступный язык
//        Session::put('locale', $locale);                    # И устанавливаем его в сессии под именем locale
//    }
    if (in_array($locale, config('app.locales'))) {   # Проверяем, что у пользователя выбран доступный язык
        Session::put('locale', $locale);                    # И устанавливаем его в сессии под именем locale

    }

    return redirect()->back()->withInput();                 # Редиректим его <s>взад</s> на ту же страницу

});

Route::post('/home/glo_store', 'HomeController@glo_store')
    ->name('home.glo_store')
    ->middleware('auth');

// Templates
Route::get('/template/index', 'TemplateController@index')
    ->name('template.index')
    ->middleware('auth');

Route::get('/template/show/{template}', 'TemplateController@show')
    ->name('template.show')
    ->middleware('auth');

Route::get('/template/create', 'TemplateController@create')
    ->name('template.create')
    ->middleware('auth');

Route::get('/template/edit/{template}', 'TemplateController@edit')
    ->name('template.edit')
    ->middleware('auth');

Route::post('/template/store', 'TemplateController@store')
    ->name('template.store')
    ->middleware('auth');

Route::put('/template/edit/{template}', 'TemplateController@update')
    ->name('template.update')
    ->middleware('auth');

Route::get('/template/delete_question/{template}', 'TemplateController@delete_question')
    ->name('template.delete_question')
    ->middleware('auth');

Route::delete('/template/delete/{template}', 'TemplateController@delete')
    ->name('template.delete')
    ->middleware('auth');

// Moderations
Route::get('/moderation/index', 'ModerationController@index')
    ->name('moderation.index')
    ->middleware('auth');

Route::get('/moderation/show/{item}', 'ModerationController@show')
    ->name('moderation.show')
    ->middleware('auth');

Route::get('/moderation/edit/{item}', 'ModerationController@edit')
    ->name('moderation.edit')
    ->middleware('auth');

Route::put('/moderation/edit/{item}', 'ModerationController@update')
    ->name('moderation.update')
    ->middleware('auth');

Route::get('/moderation/delete_question/{item}', 'ModerationController@delete_question')
    ->name('moderation.delete_question')
    ->middleware('auth');

Route::delete('/moderation/delete/{item}', 'ModerationController@delete')
    ->name('moderation.delete')
    ->middleware('auth');

// Users
Route::get('/user/index', 'UserController@index')
    ->name('user.index')
    ->middleware('auth');

Route::get('/user/show/{user}', 'UserController@show')
    ->name('user.show')
    ->middleware('auth');

Route::get('/user/create', 'UserController@create')
    ->name('user.create')
    ->middleware('auth');

Route::get('/user/edit/{user}', 'UserController@edit')
    ->name('user.edit')
    ->middleware('auth');

Route::get('/user/change_password/{user}', 'UserController@change_password')
    ->name('user.change_password')
    ->middleware('auth');

Route::post('/user/store', 'UserController@store')
    ->name('user.store')
    ->middleware('auth');

Route::put('/user/edit/{user}', 'UserController@update')
    ->name('user.update')
    ->middleware('auth');

Route::get('/user/delete_question/{user}', 'UserController@delete_question')
    ->name('user.delete_question')
    ->middleware('auth');

Route::delete('/user/delete/{user}', 'UserController@delete')
    ->name('user.delete')
    ->middleware('auth');

// Roles
Route::get('/role/index/{template}', 'RoleController@index')
    ->name('role.index')
    ->middleware('auth');

Route::get('/role/show/{role}', 'RoleController@show')
    ->name('role.show')
    ->middleware('auth');

Route::get('/role/create/{template}', 'RoleController@create')
    ->name('role.create')
    ->middleware('auth');

Route::get('/role/edit/{role}', 'RoleController@edit')
    ->name('role.edit')
    ->middleware('auth');

Route::post('/role/store', 'RoleController@store')
    ->name('role.store')
    ->middleware('auth');

Route::put('/role/edit/{role}', 'RoleController@update')
    ->name('role.update')
    ->middleware('auth');

Route::get('/role/delete_question/{role}', 'RoleController@delete_question')
    ->name('role.delete_question')
    ->middleware('auth');

Route::delete('/role/delete/{role}', 'RoleController@delete')
    ->name('role.delete')
    ->middleware('auth');

// Robas
Route::get('/roba/index_role/{role}', 'RobaController@index_role')
    ->name('roba.index_role')
    ->middleware('auth');

Route::get('/roba/index_base/{base}', 'RobaController@index_base')
    ->name('roba.index_base')
    ->middleware('auth');

Route::get('/roba/show_role/{roba}', 'RobaController@show_role')
    ->name('roba.show_role')
    ->middleware('auth');

Route::get('/roba/show_base/{roba}', 'RobaController@show_base')
    ->name('roba.show_base')
    ->middleware('auth');

Route::get('/roba/create_role/{role}', 'RobaController@create_role')
    ->name('roba.create_role')
    ->middleware('auth');

Route::get('/roba/create_base/{base}', 'RobaController@create_base')
    ->name('roba.create_base')
    ->middleware('auth');

Route::get('/roba/edit_role/{roba}', 'RobaController@edit_role')
    ->name('roba.edit_role')
    ->middleware('auth');

Route::get('/roba/edit_base/{roba}', 'RobaController@edit_base')
    ->name('roba.edit_base')
    ->middleware('auth');

Route::post('/roba/store', 'RobaController@store')
    ->name('roba.store')
    ->middleware('auth');

Route::put('/roba/edit/{roba}', 'RobaController@update')
    ->name('roba.update')
    ->middleware('auth');

Route::get('/roba/delete_question/{roba}', 'RobaController@delete_question')
    ->name('roba.delete_question')
    ->middleware('auth');

Route::delete('/roba/delete/{roba}', 'RobaController@delete')
    ->name('roba.delete')
    ->middleware('auth');

// Sets
Route::get('/set/index/{template}', 'SetController@index')
    ->name('set.index')
    ->middleware('auth');

Route::get('/set/show/{set}', 'SetController@show')
    ->name('set.show')
    ->middleware('auth');

Route::get('/set/create/{template}', 'SetController@create')
    ->name('set.create')
    ->middleware('auth');

Route::get('/set/edit/{set}', 'SetController@edit')
    ->name('set.edit')
    ->middleware('auth');

Route::post('/set/store', 'SetController@store')
    ->name('set.store')
    ->middleware('auth');

Route::put('/set/edit/{set}', 'SetController@update')
    ->name('set.update')
    ->middleware('auth');

Route::get('/set/delete_question/{set}', 'SetController@delete_question')
    ->name('set.delete_question')
    ->middleware('auth');

Route::delete('/set/delete/{set}', 'SetController@delete')
    ->name('set.delete')
    ->middleware('auth');

// Rolis
Route::get('/roli/index_role/{role}', 'RoliController@index_role')
    ->name('roli.index_role')
    ->middleware('auth');

Route::get('/roli/index_link/{link}', 'RoliController@index_link')
    ->name('roli.index_link')
    ->middleware('auth');

Route::get('/roli/show_role/{roli}', 'RoliController@show_role')
    ->name('roli.show_role')
    ->middleware('auth');

Route::get('/roli/show_link/{roli}', 'RoliController@show_link')
    ->name('roli.show_link')
    ->middleware('auth');

Route::get('/roli/create_role/{role}', 'RoliController@create_role')
    ->name('roli.create_role')
    ->middleware('auth');

Route::get('/roli/create_link/{link}', 'RoliController@create_link')
    ->name('roli.create_link')
    ->middleware('auth');

Route::get('/roli/edit_role/{roli}', 'RoliController@edit_role')
    ->name('roli.edit_role')
    ->middleware('auth');

Route::get('/roli/edit_link/{roli}', 'RoliController@edit_link')
    ->name('roli.edit_link')
    ->middleware('auth');

Route::post('/roli/store', 'RoliController@store')
    ->name('roli.store')
    ->middleware('auth');

Route::put('/roli/edit/{roli}', 'RoliController@update')
    ->name('roli.update')
    ->middleware('auth');

Route::get('/roli/delete_question/{roli}', 'RoliController@delete_question')
    ->name('roli.delete_question')
    ->middleware('auth');

Route::delete('/roli/delete/{roli}', 'RoliController@delete')
    ->name('roli.delete')
    ->middleware('auth');

// Projects
Route::get('/project/index_template/{template}', 'ProjectController@index_template')
    ->name('project.index_template')
    ->middleware('auth');

Route::get('/project/index_user/{user}', 'ProjectController@index_user')
    ->name('project.index_user')
    ->middleware('auth');

Route::get('/project/show_template/{project}', 'ProjectController@show_template')
    ->name('project.show_template')
    ->middleware('auth');

Route::get('/project/show_user/{project}', 'ProjectController@show_user')
    ->name('project.show_user')
    ->middleware('auth');

Route::get('/project/create_template/{template}', 'ProjectController@create_template')
    ->name('project.create_template')
    ->middleware('auth');

Route::get('/project/create_user/{user}', 'ProjectController@create_user')
    ->name('project.create_user')
    ->middleware('auth');

Route::get('/project/edit_template/{project}', 'ProjectController@edit_template')
    ->name('project.edit_template')
    ->middleware('auth');

Route::get('/project/edit_user/{project}', 'ProjectController@edit_user')
    ->name('project.edit_user')
    ->middleware('auth');

Route::post('/project/store', 'ProjectController@store')
    ->name('project.store')
    ->middleware('auth');

Route::put('/project/edit/{project}', 'ProjectController@update')
    ->name('project.update')
    ->middleware('auth');

Route::get('/project/delete_question/{project}', 'ProjectController@delete_question')
    ->name('project.delete_question')
    ->middleware('auth');

Route::delete('/project/delete/{project}', 'ProjectController@delete')
    ->name('project.delete')
    ->middleware('auth');

// Accesses
Route::get('/access/index_project/{project}', 'AccessController@index_project')
    ->name('access.index_project')
    ->middleware('auth');

Route::get('/access/index_user/{user}', 'AccessController@index_user')
    ->name('access.index_user')
    ->middleware('auth');

Route::get('/access/index_role/{role}', 'AccessController@index_role')
    ->name('access.index_role')
    ->middleware('auth');

Route::get('/access/show_project/{access}', 'AccessController@show_project')
    ->name('access.show_project')
    ->middleware('auth');

Route::get('/access/show_user/{access}', 'AccessController@show_user')
    ->name('access.show_user')
    ->middleware('auth');

Route::get('/access/show_role/{access}', 'AccessController@show_role')
    ->name('access.show_role')
    ->middleware('auth');

Route::get('/access/create_project/{project}', 'AccessController@create_project')
    ->name('access.create_project')
    ->middleware('auth');

Route::get('/access/create_user/{user}', 'AccessController@create_user')
    ->name('access.create_user')
    ->middleware('auth');

Route::get('/access/create_role/{role}', 'AccessController@create_role')
    ->name('access.create_role')
    ->middleware('auth');

Route::get('/access/edit_project/{access}', 'AccessController@edit_project')
    ->name('access.edit_project')
    ->middleware('auth');

Route::get('/access/edit_user/{access}', 'AccessController@edit_user')
    ->name('access.edit_user')
    ->middleware('auth');

Route::get('/access/edit_role/{access}', 'AccessController@edit_role')
    ->name('access.edit_role')
    ->middleware('auth');

Route::post('/access/store', 'AccessController@store')
    ->name('access.store')
    ->middleware('auth');

Route::put('/access/edit/{access}', 'AccessController@update')
    ->name('access.update')
    ->middleware('auth');

Route::get('/access/delete_question/{access}', 'AccessController@delete_question')
    ->name('access.delete_question')
    ->middleware('auth');

Route::delete('/access/delete/{access}', 'AccessController@delete')
    ->name('access.delete')
    ->middleware('auth');

Route::get('/access/get_roles_options_from_project/{project}', 'AccessController@get_roles_options_from_project')
    ->name('access.get_roles_options_from_project')
    ->middleware('auth');

Route::get('/access/get_roles_options_from_user_project/{user}/{project}', 'AccessController@get_roles_options_from_user_project')
    ->name('access.get_roles_options_from_user_project')
    ->middleware('auth');

// Modules
Route::get('/module/index/{task}', 'ModuleController@index')
    ->name('module.index')
    ->middleware('auth');

Route::get('/module/show/{module}', 'ModuleController@show')
    ->name('module.show')
    ->middleware('auth');

Route::get('/module/create/{task}', 'ModuleController@create')
    ->name('module.create')
    ->middleware('auth');

Route::get('/module/edit/{module}', 'ModuleController@edit')
    ->name('module.edit')
    ->middleware('auth');

Route::post('/module/store', 'ModuleController@store')
    ->name('module.store')
    ->middleware('auth');

Route::put('/module/edit/{module}', 'ModuleController@update')
    ->name('module.update')
    ->middleware('auth');

Route::get('/module/delete_question/{module}', 'ModuleController@delete_question')
    ->name('module.delete_question')
    ->middleware('auth');

Route::delete('/module/delete/{module}', 'ModuleController@delete')
    ->name('module.delete')
    ->middleware('auth');

// Bases
Route::get('/base/index/{template}', 'BaseController@index')
    ->name('base.index')
    ->middleware('auth');

Route::get('/base/template_index/{template}', 'BaseController@template_index')
    ->name('base.template_index')
    ->middleware('auth');

Route::get('/base/show/{base}', 'BaseController@show')
    ->name('base.show')
    ->middleware('auth');

Route::get('/base/create/{template}', 'BaseController@create')
    ->name('base.create')
    ->middleware('auth');

Route::get('/base/edit/{base}', 'BaseController@edit')
    ->name('base.edit')
    ->middleware('auth');

Route::post('/base/store', 'BaseController@store')
    ->name('base.store')
    ->middleware('auth');

Route::put('/base/edit/{base}', 'BaseController@update')
    ->name('base.update')
    ->middleware('auth');

Route::get('/base/delete_question/{base}', 'BaseController@delete_question')
    ->name('base.delete_question')
    ->middleware('auth');

Route::delete('/base/delete/{base}', 'BaseController@delete')
    ->name('base.delete')
    ->middleware('auth');

Route::get('/base/getBasesAll', 'BaseController@getBasesAll')
    ->name('base.getBasesAll');


// Links

Route::get('/link/index', 'LinkController@index')
    ->name('link.index')
    ->middleware('auth');

Route::get('/link/show/{link}', 'LinkController@show')
    ->name('link.show')
    ->middleware('auth');

Route::get('/link/create/{base}', 'LinkController@create')
    ->name('link.create')
    ->middleware('auth');

Route::get('/link/edit/{link}/{base}', 'LinkController@edit')
    ->name('link.edit')
    ->middleware('auth');

Route::post('/link/store', 'LinkController@store')
    ->name('link.store')
    ->middleware('auth');

Route::put('/link/edit/{link}', 'LinkController@update')
    ->name('link.update')
    ->middleware('auth');

Route::get('/link/delete_question/{link}', 'LinkController@delete_question')
    ->name('link.delete_question')
    ->middleware('auth');

Route::delete('/link/delete/{link}', 'LinkController@delete')
    ->name('link.delete')
    ->middleware('auth');

Route::get('/link/get_parent_parent_related_start_link_id/{base}/{link_current?}', 'LinkController@get_parent_parent_related_start_link_id')
    ->name('link.get_parent_parent_related_start_link_id')
    ->middleware('auth');

Route::get('/link/get_parent_child_related_start_link_id/{base}/{link_current?}', 'LinkController@get_parent_child_related_start_link_id')
    ->name('link.get_parent_child_related_start_link_id')
    ->middleware('auth');

Route::get('/link/get_tree_from_link_id/{link_start}', 'LinkController@get_tree_from_link_id')
    ->name('link.get_tree_from_link_id')
    ->middleware('auth');

Route::get('/link/get_parent_base_id_from_link_id/{link}', 'LinkController@get_parent_base_id_from_link_id')
    ->name('link.get_parent_base_id_from_link_id')
    ->middleware('auth');

Route::get('/link/base_index/{base}', 'LinkController@base_index')
    ->name('link.base_index')
    ->middleware('auth');


// Items

Route::get('/item/index', 'ItemController@index')
    ->name('item.index')
    ->middleware('auth');

Route::get('/item/base_index/{base}', 'ItemController@base_index')
    ->name('item.base_index')
    ->middleware('auth');

Route::get('/item/item_index/{item}/{par_link?}', 'ItemController@item_index')
    ->name('item.item_index')
    ->middleware('auth');

Route::get('/item/show/{item}', 'ItemController@show')
    ->name('item.show')
    ->middleware('auth');

Route::get('/item/create', 'ItemController@create')
    ->name('item.create')
    ->middleware('auth');

Route::get('/item/ext_show/{item}', 'ItemController@ext_show')
    ->name('item.ext_show')
    ->middleware('auth');

Route::get('/item/ext_create/{base}/{heading?}/{par_link?}/{parent_item?}', 'ItemController@ext_create')
    ->name('item.ext_create')
    ->middleware('auth');

Route::get('/item/edit/{item}', 'ItemController@edit')
    ->name('item.edit')
    ->middleware('auth');

Route::get('/item/ext_edit/{item}/{par_link?}/{parent_item?}', 'ItemController@ext_edit')
    ->name('item.ext_edit')
    ->middleware('auth');

Route::post('/item/store', 'ItemController@store')
    ->name('item.store')
    ->middleware('auth');

Route::post('/item/ext_store/{base}/{heading?}', 'ItemController@ext_store')
    ->name('item.ext_store')
    ->middleware('auth');

Route::put('/item/edit/{item}', 'ItemController@update')
    ->name('item.update')
    ->middleware('auth');

Route::put('/item/ext_edit/{item}', 'ItemController@ext_update')
    ->name('item.ext_update')
    ->middleware('auth');

Route::get('/item/delete_question/{item}', 'ItemController@delete_question')
    ->name('item.delete_question')
    ->middleware('auth');

Route::get('/item/ext_delete_question/{item}/{heading?}', 'ItemController@ext_delete_question')
    ->name('item.ext_delete_question')
    ->middleware('auth');

Route::delete('/item/ext_delete/{item}/{heading?}', 'ItemController@ext_delete')
    ->name('item.ext_delete')
    ->middleware('auth');

Route::post('/store_link_change', 'ItemController@store_link_change')
    ->name('item.store_link_change');

Route::get('/item/get_items_for_link/{link}', 'ItemController@get_items_for_link')
    ->name('item.get_items_for_link')
    ->middleware('auth');

Route::get('/item/get_child_items_from_parent_item/{base_start}/{item_start}/{link_result}', 'ItemController@get_child_items_from_parent_item')
    ->name('item.get_child_items_from_parent_item')
    ->middleware('auth');

Route::get('/item/get_parent_item_from_calc_child_item/{item_start}/{link_result}/{item_calc}', 'ItemController@get_parent_item_from_calc_child_item')
    ->name('item.get_parent_item_from_calc_child_item')
    ->middleware('auth');

Route::get('/item/browser/{base_id}/{sort_by_code?}/{save_by_code?}/{search?}', 'ItemController@browser')
    ->name('item.browser')
    ->middleware('auth');

Route::get('/item/calculate_name/{base}', 'ItemController@calculate_name')
    ->name('item.calculate_name')
    ->middleware('auth');

Route::get('/item/recalculation_codes/{base}', 'ItemController@recalculation_codes')
    ->name('item.recalculation_codes')
    ->middleware('auth');

Route::get('/item/item_from_base_code/{base}/{code}', 'ItemController@item_from_base_code')
    ->name('item.item_from_base_code')
    ->middleware('auth');

// Mains

Route::get('/main/index', 'MainController@index')
    ->name('main.index')
    ->middleware('auth');

Route::get('/main/show/{main}', 'MainController@show')
    ->name('main.show')
    ->middleware('auth');

Route::get('/main/create', 'MainController@create')
    ->name('main.create')
    ->middleware('auth');

Route::get('/main/edit/{main}', 'MainController@edit')
    ->name('main.edit')
    ->middleware('auth');

Route::post('/main/store', 'MainController@store')
    ->name('main.store')
    ->middleware('auth');

Route::put('/main/edit/{main}', 'MainController@update')
    ->name('main.update')
    ->middleware('auth');

Route::get('/main/delete_question/{main}', 'MainController@delete_question')
    ->name('main.delete_question')
    ->middleware('auth');

Route::delete('/main/delete/{main}', 'MainController@delete')
    ->name('main.delete')
    ->middleware('auth');

Route::get('/main/index_item/{item}', 'MainController@index_item')
    ->name('main.index_item')
    ->middleware('auth');

Route::get('/main/index_full/{item}/{link}', 'MainController@index_full')
    ->name('main.index_full')
    ->middleware('auth');

Route::post('/store_full', 'MainController@store_full')
    ->name('main.store_full');

// steps

//Route::get('/step/run_steps/{link}', 'StepController@run_steps')
//    ->name('step.run_steps')
//    ->middleware('auth');

Auth::routes();
//Route::auth();

Route::get('/home', 'HomeController@index')->name('home');

