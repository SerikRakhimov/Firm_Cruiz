<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    function glo_store(Request $request)
    {
        Session::put('glo_project_id', $request->project_id);
        Session::put('glo_role_id', $request->role_id);
        return redirect()->route('access.index_user', Auth::user());
    }

}
