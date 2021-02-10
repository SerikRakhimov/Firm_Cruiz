<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    protected $fillable = ['template_id', 'link_from_id', 'link_to_id'];

    function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    function link_from()
    {
        return $this->belongsTo(Link::class, 'link_from_id');
    }

    function link_to()
    {
        return $this->belongsTo(Link::class, 'link_to_id');
    }

    // Похожие строки в SetController.php (function store() и edit())
    // и set/edit.blade.php
    static function get_forwhats()
    {
        return array(
            "0" => trans('main.fw_group'),
            "1" => trans('main.fw_update'),
        );
    }

    static function get_updactions()
    {
        return array(
            "0" => trans('main.ua_plus'),
            "1" => trans('main.ua_minus'),
            "2" => trans('main.ua_replace'),
        );
    }

    function forwhat()
    {
        // нужно
        $result = -1;
        if ($this->is_group == true) {
            $result = 0;
        } else if ($this->is_update == true) {
            $result = 1;
        }
        return $result;
    }

    function forwhat_name()
    {
        $result = "";
        switch ($this->forwhat()) {
            case 0:
                $result = trans('main.fw_group');
                break;
            case 1:
                $result = trans('main.fw_update');
                break;
        }
        return $result;
    }

    function updaction()
    {
        // нужно
        $result = -1;
        if ($this->is_upd_plus == true) {
            $result = 0;
        } else if ($this->is_upd_minus == true) {
            $result = 1;
        } else if ($this->is_upd_replace == true) {
            $result = 2;
        }
        return $result;
    }

    function updaction_name()
    {
        $result = "";
        switch ($this->updaction()) {
            case 0:
                $result = trans('main.ua_plus');
                break;
            case 1:
                $result = trans('main.ua_minus');
                break;
            case 2:
                $result = trans('main.ua_replace');
                break;
        }
        return $result;
    }
}
