<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChequePrinted extends Model
{
    protected $fillable = [
        'cheque_id',
        'queue_id',
    ];

    protected $table = 'cheque_printed';
}
