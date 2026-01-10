<?php

namespace App;

use App\Cheque;
use Illuminate\Database\Eloquent\Model;

class ChequeSchedule extends Model
{
    const PERIODIC_ONCE = 'once';
    const PERIODIC_DAYLY = 'daily';
    const PERIODIC_WEEKLY = 'weekly';
    const PERIODIC_MONTHLY = 'monthly';

    protected $fillable = [
        'user_id',
        'expense_type_id',
        'organisation_id',
        'data',
        'period_start',
        'period_end',
        'periodic', //'monthly');
        'type',
        'no_nds',
        'sf',
        'updated_at',
        'email',
        'completed_at',
    ];

    protected $attributes = [
        'periodic' => ChequeSchedule::PERIODIC_MONTHLY,
        'type' => Cheque::TYPE_SCHEDULE,
    ];

    protected $casts = [
        'data' => 'json',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function expense_type()
    {
        return $this->belongsTo('App\ExpenseType');
    }
}
