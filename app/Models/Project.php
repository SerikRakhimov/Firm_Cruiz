<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use App\User;

class Project extends Model
{
    protected $fillable = ['name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'];


    function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function name()
    {
        $result = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = $this['name_lang_' . $index];
        }
        if ($result == "") {
            $result = $this->name_lang_0;
        };
        $result = $result . ' (' . $this->user->name . ')';
        return $result;
    }

    function dc_ext()
    {
        $result = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = $this['dc_ext_lang_' . $index];
        }
        if ($result == "") {
            $result = $this->dc_ext_lang_0;
        }
        return $result;
    }

    function dc_int()
    {
        $result = "";  // нужно, не удалять
        $index = array_search(App::getLocale(), config('app.locales'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = $this['dc_int_lang_' . $index];
        }
        if ($result == "") {
            $result = $this->dc_int_lang_0;
        }
        return $result;
    }

    function name_id()
    {
        return $this->name() . " (Id = " . strval($this->id) . ")";
    }

}
