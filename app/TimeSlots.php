<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeSlots extends Model
{
    protected $table = 'time_slots';

    protected $fillable = [
        'time_from', 'time_to', 'priority'
    ];

    public function cheques() {
    	return $this->hasMany('App\ChequeSlot', 'slot_id', 'id');
    }

    public function remain_cheques() {
    	return $this->hasMany('App\ChequeSlot', 'slot_id', 'id')->whereNull('completed_at');
    }
}
