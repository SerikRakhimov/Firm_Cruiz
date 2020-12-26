<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Project;
use App\Models\Template;
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

    function index(Template $template)
    {
        $projects = Project::where('template_id', $template->id);
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    $projects = $projects->orderBy('name_lang_0');
                    break;
                case 1:
                    $projects = $projects->orderBy('name_lang_1')->orderBy('name_lang_0');
                    break;
                case 2:
                    $projects = $projects->orderBy('name_lang_2')->orderBy('name_lang_0');
                    break;
                case 3:
                    $projects = $projects->orderBy('name_lang_3')->orderBy('name_lang_0');
                    break;
            }
        }
        session(['projects_previous_url' => request()->url()]);
        return view('project/index', ['template' => $template, 'projects' => $projects->paginate(60)]);
    }

    function show(Project $project)
    {
        $template = Template::findOrFail($project->template_id);
        return view('project/show', ['type_form' => 'show', 'template' => $template, 'project' => $project]);
    }


    function create(Template $template)
    {
        $users = User::orderBy('name')->get();
        return view('project/edit', ['template' => $template, 'users' => $users]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $project = new Project($request->except('_token', '_method'));
        $project->template_id = $request->template_id;

        $this->set($request, $project);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Project $project)
    {
        if (!($project->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $project->fill($data);

        $this->set($request, $project);

        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Project &$project)
    {
        $project->name_lang_0 = $request->name_lang_0;
        $project->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $project->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $project->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $project->user_id = $request->user_id;
        //$project->user_id = Auth::user()->id;

        $project->save();
    }

    function edit(Project $project)
    {
        $template = Template::findOrFail($project->template_id);
        $users = User::orderBy('name')->get();
        return view('project/edit', ['template' => $template, 'project' => $project, 'users' => $users]);
    }

    function delete_question(Project $project)
    {
        $template = Template::findOrFail($project->template_id);
        return view('project/show', ['type_form' => 'delete_question', 'template' => $template, 'project' => $project]);
    }

    function delete(Request $request, Project $project)
    {
        $project->delete();

        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

}
