<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'inn','kpp','address','user_id','percent'
    ];
    protected $casts = [
        'percent'=>'float'
    ];
    public function user(){
        return $this->belongsTo('App\User');
    }
}
