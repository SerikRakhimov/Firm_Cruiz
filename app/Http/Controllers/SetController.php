<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Link;
use App\Models\Role;
use App\Models\Set;
use App\Models\Template;
use App\Rules\IsUniqueSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetController extends Controller
{
    protected function rules(Request $request)
    {
        return [
            'link_from_id' => ['required', new IsUniqueSet($request)],
            'link_to_id' => ['required', new IsUniqueSet($request)],
        ];
    }

    function index(Template $template)
    {
        $sets = Set::where('template_id', $template->id);
//        $name = "";  // нужно, не удалять
//        $index = array_search(session('locale'), session('glo_menu_save'));
//        if ($index !== false) {   // '!==' использовать, '!=' не использовать
//            $name = 'name_lang_' . $index;
//            $sets = $sets->orderBy($name);
//        }

        $sets = Set::select(DB::Raw('sets.*'))
            ->join('links as lf', 'sets.link_from_id', '=', 'lf.id')
            ->join('links as lt', 'sets.link_to_id', '=', 'lt.id')
            ->where('sets.template_id', $template->id)
            ->orderBy('lf.child_base_id')
            ->orderBy('lt.child_base_id')
            ->orderBy('lf.parent_base_number')
            ->orderBy('lt.parent_base_number');

        session(['sets_previous_url' => request()->url()]);
        return view('set/index', ['template' => $template, 'sets' => $sets->paginate(60)]);
    }

    function show(Set $set)
    {
        $template = Template::findOrFail($set->template_id);
        return view('set/show', ['type_form' => 'show', 'template' => $template, 'set' => $set]);
    }


    function create(Template $template)
    {
        $links = $this->select_links_template($template);
        return view('set/edit', ['template' => $template, 'links' => $links,
            'forwhats' => Set::get_forwhats(), 'updactions' => Set::get_updactions()]);
    }

    function store(Request $request)
    {
        $request->validate($this->rules($request));

        $array_mess = [];

        $this->check($request, $array_mess);
        if (count($array_mess) > 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $set = new Set($request->except('_token', '_method'));

        $this->set($request, $set);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('sets_previous_url')) {
            return redirect(session('sets_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, Set $set)
    {
        if (!(($set->link_from_id == $request->link_from_id) && ($set->link_to_id == $request->link_to_id))) {
            $request->validate($this->rules($request));
        }

        $array_mess = [];

        $this->check($request, $array_mess);
        if (count($array_mess) > 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors($array_mess);
        }

        $data = $request->except('_token', '_method');

        $set->fill($data);

        $this->set($request, $set);

        if ($request->session()->has('sets_previous_url')) {
            return redirect(session('sets_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function check(Request $request, &$array_mess)
    {
        //base_id  д.б. список
        // Одинаковые значения недопустимы
        if ($request->link_from_id == $request->link_to_id) {
            $message = trans('main.the_same_values_are_not_valid')
                . ' ("' . trans('main.link_from') . '" ' . mb_strtolower(trans('main.and')) .
                ' "' . trans('main.link_to') . '")!';;
            $array_mess['link_from_id'] = $message;
            $array_mess['link_to_id'] = $message;
            return;
        }

        $link_from = Link::find($request->link_from_id);
        $link_to = Link::find($request->link_to_id);
        //Детские Основы должны быть с признаком "Вычисляемое наименование"
        if ($link_from) {
            if ($link_to) {
                if (($link_from->child_base->is_calcname_lst == false) || ($link_to->child_base->is_calcname_lst == false)) {
                    $message = trans('main.childrens_bases_must_be_with_the_characteristic_calculated_name')
                        . ' ("' . $link_from->child_base->name() . '" ' . mb_strtolower(trans('main.and')) .
                        ' "' . $link_to->child_base->name() . '")!';;
                    $array_mess['link_from_id'] = $message;
                    $array_mess['link_to_id'] = $message;
                    return;
                }
            }
        }
        // Родительские основы должны быть одинаковыми
        if ($link_from) {
            if ($link_to) {
                if ($link_from->parent_base_id != $link_to->parent_base_id) {
                    $message = trans('main.parent_bases_must_be_the_same')
                        . ' ("' . $link_from->parent_base->name() . '" ' . mb_strtolower(trans('main.and')) .
                        ' "' . $link_to->parent_base->name() . '")!';;
                    $array_mess['link_from_id'] = $message;
                    $array_mess['link_to_id'] = $message;
                    return;
                }
            }
        }
        //Родительские основы должны быть Число
        // Обновление
        if ($request->forwhat == 1) {
            // Добавить, Отнять
            if (($request->updaction == 0) || ($request->updaction == 1)) {
                if (($link_from->parent_base->type_is_number() == false) || ($link_to->parent_base->type_is_number() == false)) {
                    $message = trans('main.parent_bases_must_be_number')
                        . ' ("' . $link_from->parent_base->name() . '" ' . mb_strtolower(trans('main.and')) .
                        ' "' . $link_to->parent_base->name() . '")!';;
                    $array_mess['link_from_id'] = $message;
                    $array_mess['link_to_id'] = $message;
                    return;
                }
            }
        }
    }

    function set(Request $request, Set &$set)
    {
        $set->template_id = $request->template_id;
        $set->link_from_id = $request->link_from_id;
        $set->link_to_id = $request->link_to_id;

        $set->is_group = false;
        $set->is_update = false;
        $set->is_upd_plus = false;
        $set->is_upd_minus = false;
        $set->is_upd_replace = false;

        // Похожие строки в SetController.php (functions: store(), edit(), check())
        // и в Set.php (functions: get_types(), type(), type_name())
        // и в Set/edit.blade.php
        switch ($request->forwhat) {
            // Группировка
            case 0:
                $set->is_group = true;
                $set->is_update = false;
                $set->is_upd_plus = false;
                $set->is_upd_minus = false;
                $set->is_upd_replace = false;
                break;
            // Обновление
            case 1:
                $set->is_group = false;
                $set->is_update = true;
                switch ($request->updaction) {
                    // Добавить
                    case 0:
                        $set->is_upd_plus = true;
                        $set->is_upd_minus = false;
                        $set->is_upd_replace = false;
                        break;
                    // Отнять
                    case 1:
                        $set->is_upd_plus = false;
                        $set->is_upd_minus = true;
                        $set->is_upd_replace = false;
                        break;
                    // Заменить
                    case 2:
                        $set->is_upd_plus = false;
                        $set->is_upd_minus = false;
                        $set->is_upd_replace = true;
                        break;
                }
                break;
        }
        $set->save();
    }

    function edit(Set $set)
    {
        $template = Template::findOrFail($set->template_id);
        $links = $this->select_links_template($template);
        return view('set/edit', ['template' => $template, 'set' => $set, 'links' => $links,
            'forwhats' => Set::get_forwhats(), 'updactions' => Set::get_updactions()]);
    }

    function delete_question(Set $set)
    {
        $template = Template::findOrFail($set->template_id);
        return view('set/show', ['type_form' => 'delete_question', 'template' => $template, 'set' => $set]);
    }

    function delete(Request $request, Set $set)
    {
        $set->delete();

        if ($request->session()->has('sets_previous_url')) {
            return redirect(session('sets_previous_url'));
        } else {
            return redirect()->back();
        }
    }

    function select_links_template(Template $template)
    {
// Проверка для вычисляемых полей
//        ->where('links.parent_is_parent_related', false)
        return Link::select(DB::Raw('links.*'))
            ->join('bases', 'links.child_base_id', '=', 'bases.id')
            ->where('bases.template_id', $template->id)
            ->where('links.parent_is_parent_related', false)
            ->orderBy('links.child_base_id')
            ->orderBy('links.parent_base_number')
            ->get();

    }
}
