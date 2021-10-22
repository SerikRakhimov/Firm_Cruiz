<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        Было
        $this->middleware('guest')->except('logout');

//        https://askdev.ru/q/laravel-5-auth-logout-ne-rabotaet-199140/
// https://stackoverflow.com/questions/34479994/laravel-5-2-authlogout-is-not-working/34667356#34667356
//        $this->middleware('guest', ['except' => ['logout', 'getLogout']]);
    }

// https://ru.stackoverflow.com/questions/622264/laravel-%D1%80%D0%B5%D0%B4%D0%B8%D1%80%D0%B5%D0%BA%D1%82-%D0%BF%D0%BE%D1%81%D0%BB%D0%B5-%D0%B0%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D0%B8
    protected function redirectTo(){
        return url('/home');
    }

    // https://laravel.ru/forum/viewtopic.php?id=2941
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        //return redirect()->route('login');
        return redirect()->route('project.all_index');
    }

}
