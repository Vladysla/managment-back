<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ProductCheck extends Model
{
    protected $fillable = [
        'type_id', 'place_id', 'count'
    ];
}
