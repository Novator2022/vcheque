<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExportCompanyInn extends Model
{
    protected $connection = 'market';
    protected $table = 'company_inn';  

    protected $fillable = [
        'company_name', 'registered_at', 'inn', 'kpp', 'no_nds'
    ];
}
