<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Param extends Model
{
    protected $fillable = [
        'name', 'value'
    ];
}
