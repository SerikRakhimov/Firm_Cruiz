<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\Project;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
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
        if (!(($access->template_id == $request->template_id) &&($access->user_id == $request->user_id) &&($access->role_id == $request->role_id))) {
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
        $project = Project::findOrFail($access->project_id);
        $users = User::orderBy('name')->get();
        $roles = Role::where('template_id', $project->template_id)->orderBy('name_lang_0')->get();
        return view('access/edit', ['project' => $project, 'access' => $access, 'users' => $users, 'roles' => $roles]);
    }

    function edit_user(Access $access)
    {
        $user = User::findOrFail($access->user_id);
        $projects = Project::get();
        $roles = Role::orderBy('name_lang_0')->get();
        return view('access/edit', ['user' => $user, 'access' => $access, 'projects' => $projects, 'roles' => $roles]);
    }

    function edit_role(Access $access)
    {
        $role = Role::findOrFail($access->role_id);
        $projects = Project::where('template_id', $role->template_id)->get();
        $users = User::orderBy('name')->get();
        return view('access/edit', ['role' => $role, 'access' => $access, 'projects' => $projects, 'users' => $users]);
    }

    function delete_question(Access $access)
    {
        $project = Project::findOrFail($access->project_id);
        return view('access/show', ['type_form' => 'delete_question', 'project' => $project, 'access' => $access]);
    }

    function delete(Request $request, Access $access)
    {
        $access->delete();

        if ($request->session()->has('accesses_previous_url')) {
            return redirect(session('accesses_previous_url'));
        } else {
            return redirect()->back();
        }
    }


}
