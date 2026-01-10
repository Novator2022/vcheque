<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExportChequeProduct extends Model
{
    protected $connection = 'market';
    protected $table = 'cheques_products';  

    protected $fillable = [
        'cheque_id', 'name'
    ];
}
