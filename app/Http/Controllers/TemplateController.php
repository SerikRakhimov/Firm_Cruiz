<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Template;
use Illuminate\Http\Request;


class TemplateController extends Controller
{
    protected function rules()
    {
        // sun
//        return [
//            'name_lang_0' => ['required', 'max:255', 'unique_with: bases, name_lang_0'],
//            'names_lang_0' => ['required', 'max:255', 'unique_with: bases, names_lang_0'],
//        ];
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
        return view('template/index', ['templates' => $templates->paginate(60)]);
    }

    function show(Template $template)
    {
        return view('template/show', ['type_form' => 'show', 'template' => $template]);
    }
}
