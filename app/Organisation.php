<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'data','expense_type_id','limit', 'modulkassa', 'is_nonds', 'is_usn15', 'auto_export', 'no_print'
    ];
    protected $casts = [
        'data' => 'array',
        'limit' => 'float',
        'modulkassa' => 'boolean',
        'is_nonds' => 'boolean',
        'is_usn15' => 'boolean',
        'auto_export' => 'boolean',
        'no_print' => 'boolean',
    ];
    public function expense_type()
    {
        return $this->belongsTo('App\ExpenseType');
    }

    private function getQuarterPeriod() {
        $d = mktime(0,0,0);
        $kv = (int)((date('n', $d)-1)/3+1);
        $year = date('y', $d);
        return [
            date('Y-m-d 00:00:00',mktime(0,0,0,($kv-1)*3+1,1,$year)),
            date('Y-m-d 23:59:59',mktime(0,0,0,($kv)*3+1,0,$year))
        ];
    }

    public function reach()
    {
        list($from, $to) = $this->getQuarterPeriod();
        $result = 0;
        // $cheques = \App\Cheque::where('status', 'success')->where('organisation_id', $this->id)->whereBetween('created_at', [$from, $to])->get();
        $cheques = \App\Cheque::where('sf', 1)->whereIn('status', ['new', 'request', 'inprogress', 'success'])->where('organisation_id', $this->id)->whereBetween('created_at', [$from, $to])->get();
        foreach ($cheques as $cheque) {
            foreach ($cheque->data['positions'] as $item) {
                $result += ($item['quantity'] * $item['amount']);
            }
        }

        return round($result, 2);
    }
}
