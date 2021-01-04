<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Template;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected function rules()
    {
        return [
            'name_lang_0' => ['required', 'max:255'],
        ];
    }

    function index(Template $template)
    {
        $roles = Role::where('template_id', $template->id);
        $name = "";  // нужно, не удалять
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $roles = $roles->orderBy($name);
        }
        session(['roles_previous_url' => request()->url()]);
        return view('role/index', ['template' => $template, 'roles' => $roles->paginate(60)]);
    }

    function show(Role $role)
    {
        $template = Template::findOrFail($role->template_id);
        return view('role/show', ['type_form' => 'show', 'template' => $template, 'role' => $role]);
    }


    function create(Template $template)
    {
        return view('role/edit', ['template' => $template]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $role = new Role($request->except('_token', '_method'));
        $role->template_id = $request->template_id;

        $this->set($request, $role);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('roles_previous_url')) {
            return redirect(session('roles_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Role $role)
    {
        if (!($role->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $role->fill($data);

        $this->set($request, $role);

        if ($request->session()->has('roles_previous_url')) {
            return redirect(session('roles_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Role &$role)
    {
        $role->name_lang_0 = $request->name_lang_0;
        $role->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $role->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $role->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $role->is_default_for_external = isset($request->is_default_for_external) ? true : false;

        $role->save();
    }

    function edit(Role $role)
    {
        $template = Template::findOrFail($role->template_id);
        return view('role/edit', ['template' => $template, 'role' => $role]);
    }

    function delete_question(Role $role)
    {
        $template = Template::findOrFail($role->template_id);
        return view('role/show', ['type_form' => 'delete_question', 'template' => $template, 'role' => $role]);
    }

    function delete(Request $request, Role $role)
    {
        $role->delete();

        if ($request->session()->has('roles_previous_url')) {
            return redirect(session('roles_previous_url'));
        } else {
            return redirect()->back();
        }
    }

}
