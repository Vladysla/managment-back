<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transfer extends Model
{
    protected $table = 'transfer';

    public function product_sum()
    {
        return $this->belongsTo('App\ProductSum', 'product_id')->with(['product', 'size', 'color']);
    }

    public function from_place()
    {
        return $this->belongsTo('App\Place', 'from_place');
    }

    public function to_place()
    {
        return $this->belongsTo('App\Place', 'to_place');
    }
}
