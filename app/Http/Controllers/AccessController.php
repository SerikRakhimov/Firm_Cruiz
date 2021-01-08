<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\Project;
use App\Models\Role;
use App\Models\Template;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\IsUniqueAccess;

class AccessController extends Controller
{
    protected function rules(Request $request)
    {
        return [
            'project_id' => ['required', new IsUniqueAccess($request)],
            'user_id' => ['required', new IsUniqueAccess($request)],
            'role_id' => ['required', new IsUniqueAccess($request)],
        ];
    }

    function index_project(Project $project)
    {
        $accesses = Access::where('project_id', $project->id);
        $accesses = $accesses->orderBy('project_id')->orderBy('user_id')->orderBy('role_id');
        session(['accesses_previous_url' => request()->url()]);
        return view('access/index', ['project' => $project, 'accesses' => $accesses->paginate(60)]);
    }

    function index_user(User $user)
    {
        $accesses = Access::where('user_id', $user->id);
        $accesses = $accesses->orderBy('project_id')->orderBy('user_id')->orderBy('role_id');
        session(['accesses_previous_url' => request()->url()]);
        return view('access/index', ['user' => $user, 'accesses' => $accesses->paginate(60)]);
    }

    function index_role(Role $role)
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        }
        $accesses = Access::where('role_id', $role->id);
        $accesses = $accesses->orderBy('project_id')->orderBy('user_id')->orderBy('role_id');
        session(['accesses_previous_url' => request()->url()]);
        return view('access/index', ['role' => $role, 'accesses' => $accesses->paginate(60)]);
    }

    function show_project(Access $access)
    {
        $project = Project::findOrFail($access->project_id);
        return view('access/show', ['type_form' => 'show', 'project' => $project, 'access' => $access]);
    }

    function show_user(Access $access)
    {
        $user = User::findOrFail($access->user_id);
        return view('access/show', ['type_form' => 'show', 'user' => $user, 'access' => $access]);
    }

    function show_role(Access $access)
    {
        $role = Role::findOrFail($access->role_id);
        return view('access/show', ['type_form' => 'show', 'role' => $role, 'access' => $access]);
    }

    function create_project(Project $project)
    {
        $users = User::orderBy('name')->get();
        $roles = Role::where('template_id', $project->template_id)->orderBy('name_lang_0')->get();
        return view('access/edit', ['project' => $project, 'users' => $users, 'roles' => $roles]);
    }

    function create_user(User $user)
    {
        $projects = Project::get();
        $roles = Role::orderBy('name_lang_0')->get();
        return view('access/edit', ['user' => $user, 'projects' => $projects, 'roles' => $roles]);
    }

    function create_role(Role $role)
    {
        $projects = Project::where('template_id', $role->template_id)->get();
        $users = User::orderBy('name')->get();
        return view('access/edit', ['role' => $role, 'projects' => $projects, 'users' => $users]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules($request));

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $access = new Access($request->except('_token', '_method'));

        $this->set($request, $access);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('accesses_previous_url')) {
            return redirect(session('accesses_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Access $access)
    {
        if (!(Auth::user()->isAdmin() || ($access->role->is_default_for_external == true))) {
            return null;
        }
        if (!(($access->template_id == $request->template_id) && ($access->user_id == $request->user_id) && ($access->role_id == $request->role_id))) {
            $request->validate($this->rules($request));
        }

        $data = $request->except('_token', '_method');

        $access->fill($data);

        $this->set($request, $access);

        if ($request->session()->has('accesses_previous_url')) {
            return redirect(session('accesses_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Access &$access)
    {
        $access->project_id = $request->project_id;
        $access->user_id = $request->user_id;
        $access->role_id = $request->role_id;

        $access->save();
    }

    function edit_project(Access $access)
    {
//        if (!(Auth::user()->isAdmin() ||!($access->role->is_default_for_external == false))) {
//            return null;
//        }
        $project = Project::findOrFail($access->project_id);
        $users = User::orderBy('name')->get();
        $roles = Role::where('template_id', $project->template_id)->orderBy('name_lang_0')->get();

        if (!Auth::user()->isAdmin()) {
            $roles = $roles->where('is_default_for_external', true);
        }

        return view('access/edit', ['project' => $project, 'access' => $access, 'users' => $users, 'roles' => $roles]);
    }

    function edit_user(Access $access)
    {
//            if (!(Auth::user()->isAdmin() ||!($access->role->is_default_for_external == false))){
//            return null;
//        }
        $user = User::findOrFail($access->user_id);
        $projects = Project::get();

        // см. функцию get_roles_options_from_project() - в ней прописаны $roles для случая, если передан $user
        $roles = Role::all();

        return view('access/edit', ['user' => $user, 'access' => $access, 'projects' => $projects, 'roles' => $roles]);
    }

    function edit_role(Access $access)
    {
//        if (!Auth::user()->isAdmin()) {
//            return null;
//        }
        $role = Role::findOrFail($access->role_id);
        $projects = Project::where('template_id', $role->template_id)->get();
        $users = User::orderBy('name')->get();
        return view('access/edit', ['role' => $role, 'access' => $access, 'projects' => $projects, 'users' => $users]);
    }

    function delete_question(Access $access)
    {
        if (!(Auth::user()->isAdmin() || ($access->role->is_default_for_external == true))) {
            return null;
        }
        $project = Project::findOrFail($access->project_id);
        return view('access/show', ['type_form' => 'delete_question', 'project' => $project, 'access' => $access]);
    }

    function delete(Request $request, Access $access)
    {
        if (!(Auth::user()->isAdmin() || ($access->role->is_default_for_external == true))) {
            return null;
        }
        $access->delete();

        if ($request->session()->has('accesses_previous_url')) {
            return redirect(session('accesses_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    static function get_roles_options_from_project(Project $project)
    {
        $result_roles_options = "";
        if ($project != null) {
            $name = "";  // нужно, не удалять
            $index = array_search(session('locale'), session('glo_menu_save'));
            if ($index !== false) {   // '!==' использовать, '!=' не использовать
                $name = 'name_lang_' . $index;
            }
            // список roles по выбранному project/template
            $result_roles = Role::where('template_id', $project->template_id)->orderBy($name)->get();
            if (!Auth::user()->isAdmin()) {
                $result_roles = $result_roles->where('is_default_for_external', true);
            }
            foreach ($result_roles as $role) {
                $result_roles_options = $result_roles_options . "<option value='" . $role->id . "'>" . $role->name() . "</option>";
            }

        }
        return [
            'result_roles_options' => $result_roles_options
        ];
    }

    static function get_roles_options_from_user_project(User $user, Project $project)
    {
        $result_roles_options = "";
        if ($project != null) {
            $name = "";  // нужно, не удалять
            $index = array_search(session('locale'), session('glo_menu_save'));
            if ($index !== false) {   // '!==' использовать, '!=' не использовать
                $name = 'name_lang_' . $index;
            }
            // список access->role->id по переданным user/project
            $accesses_roles = Access::select('role_id')->where('project_id', $project->id)->where('user_id', $user->id)->groupBy('role_id')->get();

            foreach ($accesses_roles as $access_role) {
                $result_roles_options = $result_roles_options . "<option value='" . $access_role->role_id . "'>" . $access_role->role->name() . "</option>";
            }

        }
        return [
            'result_roles_options' => $result_roles_options
        ];
    }

}
