<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;


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
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            switch ($index) {
                case 0:
                    $templates = Template::orderBy('name_lang_0');
                    break;
                case 1:
                    $templates = Template::orderBy('name_lang_1')->orderBy('name_lang_0');
                    break;
                case 2:
                    $templates = Template::orderBy('name_lang_2')->orderBy('name_lang_0');
                    break;
                case 3:
                    $templates = Template::orderBy('name_lang_3')->orderBy('name_lang_0');
                    break;
            }
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

        return view('template/edit');
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $template = new Template($request->except('_token', '_method'));

        $this->set($request, $template);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('templates_previous_url')) {
            return redirect(session('templates_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Template $template)
    {
        if (!($template->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $template->fill($data);

        $this->set($request, $template);

        if ($request->session()->has('templates_previous_url')) {
            return redirect(session('templates_previous_url'));;
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
        return view('template/edit', ['template' => $template]);
    }

    function delete_question(Template $template)
    {
        return view('template/show', ['type_form' => 'delete_question', 'template' => $template]);
    }

    function delete(Request $request, Template $template)
    {
        $template->delete();

        if ($request->session()->has('templates_previous_url')) {
            return redirect(session('templates_previous_url'));;
        } else {
            return redirect()->back();
        }
    }
}
