<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'description', 'cheque_id'
    ];
}
