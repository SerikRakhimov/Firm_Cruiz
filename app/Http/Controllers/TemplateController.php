<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TemplateController extends Controller
{
    protected function rules()
    {
        return [
            'name_lang_0' => ['required', 'max:255'],
        ];
    }

    function index()
    {
        $templates = null;
        $index = array_search(App::getLocale(), config('app.locales'));
        $name = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $name = 'name_lang_' . $index;
            $templates = Template::orderBy($name);
        }
        session(['templates_previous_url' => request()->url()]);
        return view('template/index', ['templates' => $templates->paginate(60)]);
    }

    function show(Template $template)
    {
        return view('template/show', ['type_form' => 'show', 'template' => $template]);
    }


    function create()
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        }
        return view('template/edit');
    }

    function store(Request $request)
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        }
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $template = new Template($request->except('_token', '_method'));

        $this->set($request, $template);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('templates_previous_url')) {
            return redirect(session('templates_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Template $template)
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        }
        if (!($template->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $template->fill($data);

        $this->set($request, $template);

        if ($request->session()->has('templates_previous_url')) {
            return redirect(session('templates_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, Template &$template)
    {
        $template->name_lang_0 = $request->name_lang_0;
        $template->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $template->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $template->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $template->save();
    }

    function edit(Template $template)
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        }
        return view('template/edit', ['template' => $template]);
    }

    function delete_question(Template $template)
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        }
        return view('template/show', ['type_form' => 'delete_question', 'template' => $template]);
    }

    function delete(Request $request, Template $template)
    {
        if (!
        Auth::user()->isAdmin()) {
            return null;
        };

        $template->delete();

        if ($request->session()->has('templates_previous_url')) {
            return redirect(session('templates_previous_url'));
        } else {
            return redirect()->back();
        }
    }

}
