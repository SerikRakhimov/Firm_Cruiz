<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\Base;
use App\Models\Item;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Models\Project;
use App\Models\Template;
use App\Models\Role;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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
        $projects = Project::whereHas('template.roles', function ($query) {
            $query->where('is_default_for_external', true);
        })->orderBy('user_id')->orderBy('template_id')->orderBy('created_at');

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
        $projects = Project::where('user_id', GlobalController::glo_user_id())->whereHas('template.roles', function ($query) {
            $query->where('is_author', true);
        })->orderBy('user_id')->orderBy('template_id')->orderBy('created_at');
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
        $user = User::findOrFail($project->user_id);
        if (!
        Auth::user()->isAdmin()) {
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        return view('project/show', ['type_form' => 'show', 'user' => $user, 'project' => $project]);
    }

    function start(Project $project, Role $role = null)
    {
        if (!$role) {
            $role = Role::where('template_id', $project->template_id)->where('is_default_for_external', true)->first();
            if (!$role) {
                return view('message', ['message' => trans('main.role_default_for_external_not_found')]);
            }
        }
        if (GlobalController::check_project_user($project, $role) == false) {
            return view('message', ['message' => trans('main.info_user_changed')]);
        }
        $template = $project->template;
        // Порядок сортировки; обычные bases, вычисляемые bases, настройки - bases
        $bases = Base::where('template_id', $template->id)->orderBy('is_setup_lst')->orderBy('is_calculated_lst');
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    //$bases = Base::all()->sortBy('name_lang_0');
                    $bases = $bases->orderBy('name_lang_0');
                    break;
                case 1:
                    //$bases = Base::all()->sortBy(function($row){return $row->name_lang_1 . $row->name_lang_0;});
                    $bases = $bases->orderBy('name_lang_1')->orderBy('name_lang_0');
                    break;
                case 2:
                    $bases = $bases->orderBy('name_lang_2')->orderBy('name_lang_0');
                    break;
                case 3:
                    $bases = $bases->orderBy('name_lang_3')->orderBy('name_lang_0');
                    break;
            }
        }
        session(['bases_previous_url' => request()->url()]);
        return view('project/start', ['project' => $project, 'role' => $role, 'bases' => $bases->paginate(60)]);

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

        $array_mess = [];
        $this->check($request, $array_mess);

        if (count($array_mess) > 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

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

        $array_mess = [];
        $this->check($request, $array_mess);

        if (count($array_mess) > 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
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

    function check(Request $request, &$array_mess)
    {
        $template = Template::findOrFail($request->template_id);
        // Без этой команды "$is_closed = isset($request->is_closed) ? true : false;"
        // эта строка неправильно сравнивает "if ($request->is_closed != $template->is_closed_default_value)"
        $is_closed = isset($request->is_closed) ? true : false;
        if ($template->is_closed_default_value_fixed == true) {
            if ($is_closed != $template->is_closed_default_value) {
                if ($template->is_closed_default_value == true) {
                    $array_mess['is_closed'] = trans('main.is_closed_true_rule') . '!';
                } else {
                    $array_mess['is_closed'] = trans('main.is_closed_false_rule') . '!';
                }
            }
        }

        foreach (config('app.locales') as $lang_key => $lang_value) {
            $text_html_check = GlobalController::text_html_check($request['dc_ext_lang_' . $lang_key]);
            if ($text_html_check['result'] == true) {
                $array_mess['dc_ext_lang_' . $lang_key] = $text_html_check['message'] . '!';
            }

            $text_html_check = GlobalController::text_html_check($request['dc_int_lang_' . $lang_key]);
            if ($text_html_check['result'] == true) {
                $array_mess['dc_int_lang_' . $lang_key] = $text_html_check['message'] . '!';
            }
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

        $project->is_closed = isset($request->is_closed) ? true : false;

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
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('project.all_index');
        }

        $template = Template::findOrFail($project->template_id);
        $users = User::orderBy('name')->get();
        return view('project/edit', ['template' => $template, 'project' => $project, 'users' => $users]);
    }

    function edit_user(Project $project)
    {
        $user = User::findOrFail($project->user_id);
        if (!Auth::user()->isAdmin()) {
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        $templates = Template::get();
        return view('project/edit', ['user' => $user, 'project' => $project, 'templates' => $templates]);
    }

    function delete_question(Project $project)
    {
        $user = User::findOrFail($project->user_id);
        if (!Auth::user()->isAdmin()) {
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        $template = Template::findOrFail($project->template_id);
        return view('project/show', ['type_form' => 'delete_question', 'template' => $template, 'project' => $project]);
    }

    function delete(Request $request, Project $project)
    {
        $user = User::findOrFail($project->user_id);
        if (!Auth::user()->isAdmin()) {
            if (GlobalController::glo_user_id() != $user->id) {
                return redirect()->route('project.all_index');
            }
        }
        $project->delete();

        if ($request->session()->has('projects_previous_url')) {
            return redirect(session('projects_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function calculate_bases_start(Project $project, Role $role)
    {
        if (!(($project->template_id == $role->template_id) && ($role->is_author()))) {
            return;
        }
        return view('project/calculate_bases_start', ['project' => $project, 'role' => $role]);
    }

    function calculate_bases(Project $project, Role $role)
    {
        if (!(($project->template_id == $role->template_id) && ($role->is_author()))) {
            return;
        }

        echo nl2br(trans('main.calculation') . ": " . PHP_EOL);

        try {
            // начало транзакции
            DB::transaction(function ($r) use ($project) {

                $bases_to = Set::select(DB::Raw('links.child_base_id as base_id'))
                    ->join('links', 'sets.link_to_id', '=', 'links.id')
                    ->join('bases', 'links.child_base_id', '=', 'bases.id')
                    ->where('bases.template_id', $project->template_id)
                    ->distinct()
                    ->orderBy('links.child_base_id')
                    ->get();

//                $bases_from = Set::select(DB::Raw('links.child_base_id as base_id'))
//                    ->join('links', 'sets.link_from_id', '=', 'links.id')
//                    ->join('bases', 'links.child_base_id', '=', 'bases.id')
//                    ->where('bases.template_id', $project->template_id)
//                    ->distinct()
//                    ->orderBy('links.child_base_id')
//                    ->get();

                // Это условие 'where('bf.is_calculated_lst', '=', false)->where('bt.is_calculated_lst', '=', true)' означает
                // исключить sets, когда link_from->base и link_to->base являются вычисляемыми (base->is_calculated_lst=true)
                $bases_from = Set::select(DB::Raw('lf.child_base_id as base_id'))
                    ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
                    ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
                    ->join('bases as bf', 'lf.child_base_id', '=', 'bf.id')
                    ->join('bases as bt', 'lt.child_base_id', '=', 'bt.id')
                    ->where('bf.template_id', $project->template_id)
                    ->where('bf.is_calculated_lst', '=', false)
                    ->where('bt.is_calculated_lst', '=', true)
                    ->distinct()
                    ->orderBy('lf.child_base_id')
                    ->get();

                $str_records = mb_strtolower(trans('main.records'));

                foreach ($bases_to as $base_to_id) {
                    $base = Base::findOrFail($base_to_id['base_id']);
                    echo nl2br(trans('main.base') . ": " . $base->name() . " - ");
                    $items = Item::where('project_id', $project->id)->where('base_id', $base->id);
                    $count = $items->count();
                    $items->delete();
                    echo nl2br(trans('main.deleted') . " " . $count . " " . $str_records . PHP_EOL);
                }

                foreach ($bases_from as $base_from_id) {
                    $base = Base::findOrFail($base_from_id['base_id']);
                    echo nl2br(trans('main.base') . ": " . $base->name() . " - ");
                    $items = Item::where('project_id', $project->id)->where('base_id', $base->id)->get();
                    $count = $items->count();
                    foreach ($items as $item) {
                        // $reverse = true - отнимать, false - прибавлять
                        (new ItemController)->save_info_sets($item, false);
                    }
                    echo nl2br(trans('main.processed') . " " . $count . " " . $str_records . PHP_EOL);
                }

            }, 3);  // Повторить три раза, прежде чем признать неудачу
            // окончание транзакции

        } catch (Exception $exc) {
            return trans('transaction_not_completed') . ": " . $exc->getMessage();
        }

        echo '<p class="text-center">
            <a href=' . '"' . route('project.start', ['project' => $project->id, 'role' => $role]) . '" title="' . trans('main.bases') . '">' . $project->name()
            . '</a>
        </p>';

//        $set_main = Set::select(DB::Raw('sets.*, lt.child_base_id as to_child_base_id, lt.parent_base_id as to_parent_base_id'))
//            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
//            ->where('lf.child_base_id', '=', $item->base_id)
//            ->orderBy('sets.serial_number')
//            ->orderBy('sets.link_from_id')
//            ->orderBy('sets.link_to_id')->get();

        //$items = Item::joinSub($sets, 'sets', function ($join) {
        //        $join->on('items.base_id', '=', 'sets.base_id');})->get();


//        $users = DB::table('items')
//            ->joinSub($bases, 'bases', function ($join) {
//                $join->on('items.id', 1);
//            })->get();

        //dd($items);

    }

}
