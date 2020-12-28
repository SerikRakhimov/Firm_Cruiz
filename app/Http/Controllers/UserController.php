<?php

namespace App\Http\Controllers;

use App\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected function rules()
    {
        return [
            'name' => 'required|unique:users,name',
            'email' => 'required|unique:users,email',
            'password' => 'min:8',
            'confirm_password' => 'min:8|same:password'
        ];
    }

    protected function name_rules()
    {
        return [
            'name' => 'required|unique:users,name'
        ];
    }

    protected function email_rules()
    {
        return [
            'email' => 'required|unique:users,email'
        ];
    }

    protected function password_rules()
    {
        return [
            'password' => 'min:8',
            'confirm_password' => 'min:8|same:password'
        ];
    }

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
        if ($user->name != $request->name) {
            $request->validate($this->name_rules());
        }
        if ($user->email != $request->email) {
            $request->validate($this->email_rules());
        }
        if ($user->password != $request->password) {
            $request->validate($this->password_rules());
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
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->is_admin = false;
        $user->save();
    }

    function edit(User $user)
    {
        return view('user/edit', ['user' => $user, 'change_password' => false]);
    }

    function change_password(User $user)
    {
        return view('user/edit', ['user' => $user, 'change_password' => true]);
    }

    function delete_question(User $user)
    {
        if ($user->isAdmin() == true) {
            abort(404);
        } else {
            return view('user/show', ['type_form' => 'delete_question', 'user' => $user]);
        }
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
