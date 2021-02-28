<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Models\Base;
use App\Models\Roba;
use App\Models\Role;
use App\Models\Template;
use App\Rules\IsUniqueRoba;
use App\User;
use Illuminate\Http\Request;

class RobaController extends Controller
{
    protected function rules(Request $request)
    {
        return [
            'role_id' => ['required', new IsUniqueRoba($request)],
            'base_id' => ['required', new IsUniqueRoba($request)],
        ];
    }

    function index_role(Role $role)
    {
        $robas = Roba::where('role_id', $role->id)->orderBy('base_id');
        session(['robas_previous_url' => request()->url()]);
        return view('roba/index', ['role' => $role, 'robas' => $robas->paginate(60)]);
    }


    function index_base(Base $base)
    {
        $robas = Roba::where('base_id', $base->id)->orderBy('role_id');
        session(['robas_previous_url' => request()->url()]);
        return view('roba/index', ['base' => $base, 'robas' => $robas->paginate(60)]);
    }

    function show_role(Roba $roba)
    {
        $role = Role::findOrFail($roba->role_id);
        return view('roba/show', ['type_form' => 'show', 'role' => $role, 'roba' => $roba]);
    }

    function show_base(Roba $roba)
    {
        $base = Base::findOrFail($roba->base_id);
        return view('roba/show', ['type_form' => 'show', 'base' => $base, 'roba' => $roba]);
    }

    function create_role(Role $role)
    {
        $bases = Base::where('template_id', $role->template_id);
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $bases = $bases->orderBy($name);
        }
        $bases = $bases->get();
        return view('roba/edit', ['role' => $role, 'bases' => $bases]);
    }

    function create_base(Base $base)
    {
        $roles = Role::where('template_id', $base->template_id);
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $roles = $roles->orderBy($name);
        }
        $roles = $roles->get();
        return view('roba/edit', ['base' => $base, 'roles' => $roles]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules($request));

        $array_mess = [];
        $this->check($request, $array_mess);

        if (count($array_mess) > 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $roba = new Roba($request->except('_token', '_method'));

        $this->set($request, $roba);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('robas_previous_url')) {
            return redirect(session('robas_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Roba $roba)
    {
        if (!(($roba->role_id == $request->role_id) && ($roba->base_id == $request->base_id))) {
            $request->validate($this->rules($request));
        }

        $array_mess = [];
        $this->check($request, $array_mess);

        if (count($array_mess) > 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        $data = $request->except('_token', '_method');

        $roba->fill($data);

        $this->set($request, $roba);

        if ($request->session()->has('robas_previous_url')) {
            return redirect(session('robas_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function check(Request $request, &$array_mess)
    {
        if ($request->is_list_base_create == true && $request->is_edit_base_read == true) {
            $array_mess['is_edit_base_read'] = trans('main.is_list_base_create_rule') . '!';
        }
        if ($request->is_list_base_read  == true && ($request->is_list_base_create || $request->is_list_base_update ||$request->is_list_base_delete)) {
            $array_mess['is_list_base_read'] = trans('main.is_list_base_read_rule') . '!';
        }
        if ($request->is_list_base_delete  == false && $request->is_list_base_used_delete == true) {
            $array_mess['is_list_base_used_delete'] = trans('main.is_list_base_used_delete_rule') . '!';
        }
        if ($request->is_edit_base_read  == true && $request->is_edit_base_update == true) {
            $array_mess['is_edit_base_read'] = trans('main.is_edit_base_read_rule') . '!';
        }
        if ($request->is_edit_link_read  == true && $request->is_edit_link_update == true) {
            $array_mess['is_edit_link_read'] = trans('main.is_edit_link_read_rule') . '!';
        }
    }

    function set(Request $request, Roba &$roba)
    {
        $roba->role_id = $request->role_id;
        $roba->base_id = $request->base_id;
        $roba->is_all_base_calcname_enable = isset($request->is_all_base_calcname_enable) ? true : false;
        $roba->is_list_base_create = isset($request->is_list_base_create) ? true : false;
        $roba->is_list_base_read = isset($request->is_list_base_read) ? true : false;
        $roba->is_list_base_update = isset($request->is_list_base_update) ? true : false;
        $roba->is_list_base_delete = isset($request->is_list_base_delete) ? true : false;
        $roba->is_list_base_used_delete = isset($request->is_list_base_used_delete) ? true : false;
        $roba->is_list_base_byuser = isset($request->is_list_base_byuser) ? true : false;
        $roba->is_edit_base_read = isset($request->is_edit_base_read) ? true : false;
        $roba->is_edit_base_update = isset($request->is_edit_base_update) ? true : false;
        $roba->is_list_base_enable = isset($request->is_list_base_enable) ? true : false;
        $roba->is_list_link_enable = isset($request->is_list_link_enable) ? true : false;
        $roba->is_show_base_enable = isset($request->is_show_base_enable) ? true : false;
        $roba->is_show_link_enable = isset($request->is_show_link_enable) ? true : false;
        $roba->is_edit_link_read = isset($request->is_edit_link_read) ? true : false;
        $roba->is_edit_link_update = isset($request->is_edit_link_update) ? true : false;
        $roba->save();
    }

    function edit_role(Roba $roba)
    {
        $role = Role::findOrFail($roba->role_id);
        $bases = Base::where('template_id', $role->template_id);
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $bases = $bases->orderBy($name);
        }
        $bases = $bases->get();
        return view('roba/edit', ['role' => $role, 'roba' => $roba, 'bases' => $bases]);
    }

    function edit_base(Roba $roba)
    {
        $base = Base::findOrFail($roba->base_id);
        $roles = Role::where('template_id', $base->template_id);
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $roles = $roles->orderBy($name);
        }
        $roles = $roles->get();
        return view('roba/edit', ['base' => $base, 'roba' => $roba, 'roles' => $roles]);
    }

    function delete_question(Roba $roba)
    {
        $template = Template::findOrFail($roba->role->template_id);
        return view('roba/show', ['type_form' => 'delete_question', 'template' => $template, 'roba' => $roba]);
    }

    function delete(Request $request, Roba $roba)
    {
        $roba->delete();

        if ($request->session()->has('robas_previous_url')) {
            return redirect(session('robas_previous_url'));
        } else {
            return redirect()->back();
        }
    }

}
