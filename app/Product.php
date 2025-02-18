<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [ 'brand', 'model', 'price_arrival', 'price_sell', 'photo', 'type_id' ];

    public function type() {
        return $this->belongsTo('App\Type', 'type_id');
    }

}
