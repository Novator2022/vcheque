<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChequeToPrint extends Model
{
    protected $fillable = [
        'cheque_id',
        'queue_id',
        'status',
    ];

    protected $table = 'cheque_to_print';
}
