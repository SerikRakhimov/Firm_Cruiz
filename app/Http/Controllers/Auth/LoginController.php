<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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

}
