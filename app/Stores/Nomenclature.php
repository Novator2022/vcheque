<?php

namespace App\Stores;
use App\Stores\Model;

class Nomenclature extends Model
{
    protected $fillable = [
        'organisation_id', 'metadata','name','description','amount','quantity'
    ];
    protected $casts = [
        'metadata' => 'array',
        'amount' => 'float',
        'quantity' => 'integer',
    ];
    protected $model = 'Catalog_Номенклатура';
    public function organisation(){
        return $this->belongsTo('App\Organisation');
    }
}
