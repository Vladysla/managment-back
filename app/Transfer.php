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

    public function product_sum_transfer()
    {
        return $this->belongsTo('App\ProductSum', 'product_id')->with(['product.type', 'size', 'color']);
    }

    public function from_place()
    {
        return $this->belongsTo('App\Place', 'from_place');
    }

    public function to_place()
    {
        return $this->belongsTo('App\Place', 'to_place');
    }

    public static function countedIncomeProducts($place)
    {
        return DB::table('transfer')
            ->select(DB::raw("COUNT(`product_id`) as count"))
            ->where(['to_place' => $place, 'status' => '0'])->first()->count;
    }

    public static function getPendingIds($place)
    {
        return DB::table('transfer')
            ->select('product_id')
            ->where(['to_place' => $place, 'status' => '0'])->get();
    }

    public static function applyAll($place)
    {
        return DB::table('transfer')
            ->where(['to_place' => $place, 'status' => '0'])
            ->update(['status' => 1]);
    }
}
