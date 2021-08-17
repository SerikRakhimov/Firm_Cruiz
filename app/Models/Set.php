<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Http\Controllers\GlobalController;

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
            "1" => trans('main.fw_calcsort'),
            "2" => trans('main.fw_onlylink'),
            "3" => trans('main.fw_update'),
        );
    }

    function forwhat()
    {
        // нужно
        $result = -1;
        if ($this->is_group == true) {
            $result = 0;
        } else if ($this->is_calcsort == true) {
            $result = 1;
        } else if ($this->is_onlylink == true) {
            $result = 2;
        } else if ($this->is_update == true) {
            $result = 3;
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
                $result = trans('main.fw_calcsort');
                break;
            case 2:
                $result = trans('main.fw_onlylink');
                break;
            case 3:
                $result = trans('main.fw_update');
                break;
        }
        return $result;
    }

    static function get_updactions()
    {
        return array(
            "0" => trans('main.ua_pluscount'),
            "1" => trans('main.ua_minuscount'),
            "2" => trans('main.ua_plussum'),
            "3" => trans('main.ua_minussum'),
            "4" => trans('main.ua_replace'),
            "5" => trans('main.ua_cl_gr_first'),
            "6" => trans('main.ua_cl_gr_last'),
            "7" => trans('main.ua_cl_fn_min'),
            "8" => trans('main.ua_cl_fn_max'),
            "9" => trans('main.ua_cl_fn_avg'),
            "10" => trans('main.ua_cl_fn_count'),
            "11" => trans('main.ua_cl_fn_sum')
        );
    }

    function updaction()
    {
        // нужно
        $result = -1;
        if ($this->is_upd_pluscount == true) {
            $result = 0;
        } else if ($this->is_upd_minuscount == true) {
            $result = 1;
        } else if ($this->is_upd_plussum == true) {
            $result = 2;
        } else if ($this->is_upd_minussum == true) {
            $result = 3;
        } else if ($this->is_upd_replace == true) {
            $result = 4;
        } else if ($this->is_upd_cl_gr_first == true) {
            $result = 5;
        } else if ($this->is_upd_cl_gr_last == true) {
            $result = 6;
        } else if ($this->is_upd_cl_fn_min == true) {
            $result = 7;
        } else if ($this->is_upd_cl_fn_max == true) {
            $result = 8;
        } else if ($this->is_upd_cl_fn_avg == true) {
            $result = 9;
        } else if ($this->is_upd_cl_fn_count == true) {
            $result = 10;
        } else if ($this->is_upd_cl_fn_sum == true) {
            $result = 11;
        }
        return $result;
    }

//$table->boolean('is_upd_calc')->default(false);
//$table->boolean('is_upd_cl_group')->default(false);
//$table->boolean('is_upd_cl_func')->default(false);
    function updaction_name()
    {
        $result = "";
        switch ($this->updaction()) {
            case 0:
                $result = trans('main.ua_pluscount');
                break;
            case 1:
                $result = trans('main.ua_minuscount');
                break;
            case 2:
                $result = trans('main.ua_plussum');
                break;
            case 3:
                $result = trans('main.ua_minussum');
                break;
            case 4:
                $result = trans('main.ua_replace');
                break;
            case 5:
                $result = trans('main.ua_cl_gr_first');
                break;
            case 6:
                $result = trans('main.ua_cl_gr_last');
                break;
            case 7:
                $result = trans('main.ua_cl_fn_min');
                break;
            case 8:
                $result = trans('main.ua_cl_fn_max');
                break;
            case 9:
                $result = trans('main.ua_cl_fn_avg');
                break;
            case 10:
                $result = trans('main.ua_cl_fn_count');
                break;
            case 11:
                $result = trans('main.ua_cl_fn_sum');
                break;
        }
        return $result;
    }

    function updaction_delete_record_with_zero_value()
    {
        // Нужно
        $result = "";
        if ($this->is_update == true) {
            $result = GlobalController::name_is_boolean($this->is_upd_delete_record_with_zero_value);
        }
        return $result;
    }

}
