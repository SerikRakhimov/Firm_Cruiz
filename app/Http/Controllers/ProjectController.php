<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\Item;
use Illuminate\Support\Facades\App;
use App\User;
use App\Models\Project;
use App\Models\Template;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected function rules()
    {
        return [
            'name_lang_0' => ['required', 'max:255'],
        ];
    }


    function all_index()
    {
        $projects = Project::orderBy('user_id')->orderBy('template_id')->orderBy('created_at');
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $projects = $projects->orderBy($name);
        }
        //session(['projects_previous_url' => request()->url()]);
        return view('project/main_index', ['projects' => $projects->paginate(60),
            'all_projects' => true, 'my_projects' => false, 'title' => trans('main.all_projects')]);
    }

    function my_index()
    {
        $projects = Project::where('user_id', GlobalController::glo_user_id())->orderBy('user_id')->orderBy('template_id')->orderBy('created_at');
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $projects = $projects->orderBy($name);
        }
        //session(['projects_previous_url' => request()->url()]);
        return view('project/main_index', ['projects' => $projects->paginate(60),
            'all_projects' => false, 'my_projects' => true, 'title' => trans('main.my_projects')]);
    }

    function index_template(Template $template)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('project.all_index');
        }
        $projects = Project::where('template_id', $template->id);
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $projects = $projects->orderBy($name);
        }
        session(['projects_previous_url' => request()->url()]);
        return view('project/index', ['template' => $template, 'projects' => $projects->paginate(60)]);
    }

    function index_user(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        $projects = Project::where('user_id', $user->id);
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $projects = $projects->orderBy($name);
        }
        session(['projects_previous_url' => request()->url()]);
        return view('project/index', ['user' => $user, 'projects' => $projects->paginate(60)]);
    }

    function show_template(Project $project)
    {
        if (!
        Auth::user()->isAdmin()) {
            return redirect()->route('project.all_index');
        }

        $template = Template::findOrFail($project->template_id);
        return view('project/show', ['type_form' => 'show', 'template' => $template, 'project' => $project]);
    }

    function show_user(Project $project)
    {
        if (!
        Auth::user()->isAdmin()) {
            $user = User::findOrFail($project->user_id);
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        return view('project/show', ['type_form' => 'show', 'user' => $user, 'project' => $project]);
    }

    function create_template(Template $template)
    {
        if (!
        Auth::user()->isAdmin()) {
            return redirect()->route('project.all_index');
        }

        $exists = Template::whereHas('roles', function ($query) {
            $query->where('is_author', true);
        })->where('id', $template->id)->exists();
        if ($exists) {
            $users = User::orderBy('name')->get();
            return view('project/edit', ['template' => $template, 'users' => $users]);
        } else {
            return view('message', ['message' => trans('main.role_author_not_found')]);
        }
    }

    function create_user(User $user)
    {
        if (GlobalController::glo_user_id() != $user->id) {
            return redirect()->route('project.all_index');
        }

        $templates = Template::whereHas('roles', function ($query) {
            $query->where('is_author', true);
        })->get();
        if ($templates) {
            return view('project/edit', ['user' => $user, 'templates' => $templates]);
        } else {
            return view('message', ['message' => trans('main.role_author_not_found')]);
        }

    }

    function create_template_user(Template $template)
    {
        $user = GlobalController::glo_user();

        $exists = Template::whereHas('roles', function ($query) {
            $query->where('is_author', true);
        })->where('id', $template->id)->exists();
        if ($exists) {
            return view('project/edit', ['template' => $template, 'user' => $user]);
        } else {
            return view('message', ['message' => trans('main.role_author_not_found')]);
        }
    }

    function store(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        if (GlobalController::glo_user_id() != $user->id) {
            return redirect()->route('project.all_index');
        }
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $project = new Project($request->except('_token', '_method'));
        //$project->template_id = $request->template_id;

        $this->set($request, $project);

        $role = Role::where('template_id', $project->template_id)->where('is_author', true)->first();
        if ($role) {
            $access = new Access();
            $access->project_id = $project->id;
            $access->user_id = $project->user_id;
            $access->role_id = $role->id;
            $access->save();
        }

        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));
        } else {
            //return redirect()->back();
            return redirect()->route('project.my_index');
        }

    }

    function update(Request $request, Project $project)
    {
        if (!Auth::user()->isAdmin()) {
            $user = User::findOrFail($project->user_id);
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        if (!($project->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $project->fill($data);

        $this->set($request, $project);

        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Project &$project)
    {
        $project->template_id = $request->template_id;
        $project->user_id = $request->user_id;

        $project->name_lang_0 = $request->name_lang_0;
        $project->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $project->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $project->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $project->dc_ext_lang_0 = isset($request->dc_ext_lang_0) ? $request->dc_ext_lang_0 : "";
        $project->dc_ext_lang_1 = isset($request->dc_ext_lang_1) ? $request->dc_ext_lang_1 : "";
        $project->dc_ext_lang_2 = isset($request->dc_ext_lang_2) ? $request->dc_ext_lang_2 : "";
        $project->dc_ext_lang_3 = isset($request->dc_ext_lang_3) ? $request->dc_ext_lang_3 : "";

        $project->dc_int_lang_0 = isset($request->dc_int_lang_0) ? $request->dc_int_lang_0 : "";
        $project->dc_int_lang_1 = isset($request->dc_int_lang_1) ? $request->dc_int_lang_1 : "";
        $project->dc_int_lang_2 = isset($request->dc_int_lang_2) ? $request->dc_int_lang_2 : "";
        $project->dc_int_lang_3 = isset($request->dc_int_lang_3) ? $request->dc_int_lang_3 : "";

        $project->save();
    }

    function edit_template(Project $project)
    {
        if (!
        Auth::user()->isAdmin()) {
            return redirect()->route('project.all_index');
        }

        $template = Template::findOrFail($project->template_id);
        $users = User::orderBy('name')->get();
        return view('project/edit', ['template' => $template, 'project' => $project, 'users' => $users]);
    }

    function edit_user(Project $project)
    {
        $user = User::findOrFail($project->user_id);
        if (GlobalController::glo_user_id() != $user->id) {
            return redirect()->route('project.all_index');
        }
        $templates = Template::get();
        return view('project/edit', ['user' => $user, 'project' => $project, 'templates' => $templates]);
    }

    function delete_question(Project $project)
    {
        $user = User::findOrFail($project->user_id);
        if (GlobalController::glo_user_id() != $user->id) {
            return redirect()->route('project.all_index');
        }
        $template = Template::findOrFail($project->template_id);
        return view('project/show', ['type_form' => 'delete_question', 'template' => $template, 'project' => $project]);
    }

    function delete(Request $request, Project $project)
    {
        $user = User::findOrFail($project->user_id);
        if (GlobalController::glo_user_id() != $user->id) {
            return redirect()->route('project.all_index');
        }

        $project->delete();

        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));
        } else {
            return redirect()->back();
        }
    }

}
