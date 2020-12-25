<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name_lang_0', 'name_lang_1', 'name_lang_2', 'name_lang_3'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function name()
    {
        $result = "";  // нужно, не удалять
        $index = array_search(session('locale'), session('glo_menu_save'));
        if ($index !== false) {   // '!==' использовать, '!=' не использовать
            $result = $this['name_lang_' . $index];
        }
        if ($result == "") {
            $result = $this->name_lang_0;
        }
        return $result;
    }

}
