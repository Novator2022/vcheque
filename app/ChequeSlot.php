<?php

namespace App;

use App\Cheque;
use App\TimeSlots;
use Illuminate\Database\Eloquent\Model;

class ChequeSlot extends Model
{
    protected $table = 'cheque_slot';

    protected $fillable = [
        'organisation_id', 'cheque_id', 'slot_id', 'completed_at'
    ];

    public function slot() {
    	return $this->belongsTo('\App\TimeSlots', 'slot_id', 'id');
    }

    public function cheque() {
        return $this->belongsTo('App\Cheque');
    }

    public function get_slot(Cheque $cheque, $time_from = false) {
        if (!$time_from) {
            $time_from = date('H:i', strtotime($cheque->created_at));
        }

        $slot = TimeSlots::withCount([
            'cheques as cheques_count' => function ($query) use($cheque) {
                $query->where('organisation_id', $cheque->organisation_id);
                $query->whereNull('completed_at');
            }])
            ->where('time_from', '>=', $time_from)
            ->orderBy('cheques_count', 'asc')
            ->orderBy('priority', 'asc')
            ->orderBy('time_from', 'asc')
            ->inRandomOrder()
            ->limit(1)
            ->first();

        if (!$slot) {
            $slot = $this->get_slot($cheque, '09:00');
        }

        return $slot;
    }

    public function assign(Cheque $cheque) {
        if (($cheque) && (!empty($cheque->organisation_id)) && (!empty($cheque->id))) {
            $slot = $this->get_slot($cheque);

            try {
            	self::create([
                    'organisation_id' => $cheque->organisation_id,
            		'cheque_id' => $cheque->id,
            		'slot_id' => $slot->id,
            	]);
            } catch (\Exception $e) {
                \Log::debug($cheque);
                \Log::debug($slot);
                \Log::error($e);
            }
        }
    }
}
