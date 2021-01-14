<?php

namespace App\Http\Controllers;

use App\Rules\IsUniqueAccess;
use Illuminate\Http\Request;

class RobaController extends Controller
{
    protected function rules(Request $request)
    {
        return [
            'project_id' => ['required', new IsUniqueAccess($request)],
            'user_id' => ['required', new IsUniqueAccess($request)],
            'role_id' => ['required', new IsUniqueAccess($request)],
        ];
    }

}
