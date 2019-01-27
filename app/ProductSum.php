<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductSum extends Model
{
    protected $table = 'products_sum';

    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'place_id', 'type_id', 'sold', 'sold_at'
    ];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    public function color()
    {
        return $this->belongsTo('App\Color', 'color_id');
    }

    public function size()
    {
        return $this->belongsTo('App\Size', 'size_id');
    }

    public function place()
    {
        return $this->belongsTo('App\Place', 'place_id');
    }

    public function type()
    {
        return $this->belongsTo('App\Type', 'type_id');
    }

    public function getProducts()
    {

    }

    public function getTotalProducts($condition)
    {
        DB::table('products_sum')
            ->select(DB::raw("COUNT(DISTINCT `product_id`) as count"))
            ->where($condition)->first()->count;
    }

}
