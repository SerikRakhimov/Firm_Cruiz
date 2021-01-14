<?php

namespace App\Http\Controllers;

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
        $index = array_search(session('locale'), session('glo_menu_save'));
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
        $index = array_search(session('locale'), session('glo_menu_save'));
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

        $data = $request->except('_token', '_method');

        $roba->fill($data);

        $this->set($request, $roba);

        if ($request->session()->has('robas_previous_url')) {
            return redirect(session('robas_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Roba &$roba)
    {
        $roba->role_id = $request->role_id;
        $roba->base_id = $request->base_id;
        $roba->is_create = isset($request->is_create) ? true : false;
        $roba->is_read = isset($request->is_read) ? true : false;
        $roba->is_update = isset($request->is_update) ? true : false;
        $roba->is_delete = isset($request->is_delete) ? true : false;
        $roba->is_user = isset($request->is_user) ? true : false;

        $roba->save();
    }

    function edit_role(Roba $roba)
    {
        $role = Role::findOrFail($roba->role_id);
        $bases = Base::where('template_id', $role->template_id);
        $name = "";  // нужно, не удалять
        $index = array_search(session('locale'), session('glo_menu_save'));
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
        $index = array_search(session('locale'), session('glo_menu_save'));
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
