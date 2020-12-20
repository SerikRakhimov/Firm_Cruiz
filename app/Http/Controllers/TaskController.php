<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected function rules()
    {
        return [
            'name_lang_0' => ['required', 'max:255'],
        ];
    }

    function index(Template $template)
    {
        $tasks = Task::where('template_id', $template->id);
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    $tasks = $tasks->orderBy('name_lang_0');
                    break;
                case 1:
                    $tasks = $tasks->orderBy('name_lang_1')->orderBy('name_lang_0');
                    break;
                case 2:
                    $tasks = $tasks->orderBy('name_lang_2')->orderBy('name_lang_0');
                    break;
                case 3:
                    $tasks = $tasks->orderBy('name_lang_3')->orderBy('name_lang_0');
                    break;
            }
        }
        session(['previous-url' => request()->url()]);
        return view('task/index', ['template' => $template, 'tasks' => $tasks->paginate(60)]);
    }

    function show(Task $task)
    {
        return view('task/show', ['type_form' => 'show', 'task' => $task]);
    }


    function create()
    {

        return view('task/edit');
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $task = new Task($request->except('_token', '_method'));

        $this->set($request, $task);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('previous-url')) {
            return redirect(session('previous-url'));;
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Task $task)
    {
        if (!($task->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $task->fill($data);

        $this->set($request, $task);

        if ($request->session()->has('previous-url')) {
            return redirect(session('previous-url'));;
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Task &$task)
    {
        $task->name_lang_0 = $request->name_lang_0;
        $task->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $task->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $task->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $task->save();
    }

    function edit(Task $task)
    {
        return view('task/edit', ['task' => $task]);
    }

    function delete_question(Task $task)
    {
        return view('task/show', ['type_form' => 'delete_question', 'task' => $task]);
    }

    function delete(Request $request, Task $task)
    {
        $task->delete();

        if ($request->session()->has('previous-url')) {
            return redirect(session('previous-url'));;
        } else {
            return redirect()->back();
        }
    }
}
