<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    function index()
    {
        $users = User::orderBy('name');
        session(['users_previous_url' => request()->url()]);
        return view('user/index', ['users' => $users->paginate(60)]);
    }

    function show(User $user)
    {
        return view('user/show', ['type_form' => 'show', 'user' => $user]);
    }


    function create()
    {
        return view('user/edit');
    }

    function store(Request $request)
    {
        $request->validate($this->rules());

        // установка часового пояса нужно для сохранения времени
        date_default_timezone_set('Asia/Almaty');

        $user = new User($request->except('_token', '_method'));

        $this->set($request, $user);
        //https://laravel.demiart.ru/laravel-sessions/
        if ($request->session()->has('users_previous_url')) {
            return redirect(session('users_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

    function update(Request $request, User $user)
    {
        if (!($user->name_lang_0 == $request->name_lang_0)) {
            $request->validate($this->rules());
        }

        $data = $request->except('_token', '_method');

        $user->fill($data);

        $this->set($request, $user);

        if ($request->session()->has('users_previous_url')) {
            return redirect(session('users_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

    function set(Request $request, User &$user)
    {
        $user->name_lang_0 = $request->name_lang_0;
        $user->name_lang_1 = isset($request->name_lang_1) ? $request->name_lang_1 : "";
        $user->name_lang_2 = isset($request->name_lang_2) ? $request->name_lang_2 : "";
        $user->name_lang_3 = isset($request->name_lang_3) ? $request->name_lang_3 : "";

        $user->save();
    }

    function edit(User $user)
    {
        return view('user/edit', ['user' => $user]);
    }

    function delete_question(User $user)
    {
        return view('user/show', ['type_form' => 'delete_question', 'user' => $user]);
    }

    function delete(Request $request, User $user)
    {
        $user->delete();

        if ($request->session()->has('users_previous_url')) {
            return redirect(session('users_previous_url'));;
        } else {
            return redirect()->back();
        }
    }

}
