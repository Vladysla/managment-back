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

    /**
     * @param array $condition
     * @return integer
     */
    public static function getTotalProducts(array $condition)
    {
        return DB::table('products_sum')
            ->select(DB::raw("COUNT(DISTINCT `product_id`) as count"))
            ->join('products', 'products.id', 'products_sum.product_id')
            ->where($condition)->first()->count;
    }

    public static function getDistinctProducts(array $condition)
    {
        return DB::table('products_sum')->select('product_id', 'sold_at')
            ->join('products', 'products.id', 'products_sum.product_id')
            ->where($condition)
            ->orderByDesc('products.model')->distinct();
    }

    public static function getProductFullInfo($product_id, $sold)
    {
        return DB::table("products_sum")->select(DB::raw("product_id, brand, model, price_arrival, price_sell, sizes.name as size_name, colors.name as color_name, types.name as type_name, places.name as place_name, sold_at, COUNT(*) as products_count"))
            ->join('colors', 'colors.id', 'products_sum.color_id')
            ->join('sizes', 'sizes.id', 'products_sum.size_id')
            ->join('products', 'products.id', 'products_sum.product_id')
            ->join('types', 'types.id', 'products.type_id')
            ->join('places', 'places.id', 'products_sum.place_id')
            ->where('product_id', $product_id)
            ->where('sold', $sold)
            ->groupBy('product_id', 'sizes.name', 'colors.name', 'places.name', 'sold_at')->get();
    }

    public static function getProductInfo($product_id, $sold)
    {
        $sold == "NO" && $sold = '0';
        return DB::select(DB::raw("SELECT 
                product_id, brand, model, price_arrival, price_sell, types.name as type_name, sold_at, 
                (SELECT COUNT(*) FROM products_sum WHERE product_id = $product_id AND sold = 0) as avilable_count,
                (SELECT COUNT(*) FROM products_sum WHERE product_id = $product_id AND sold = 1) as sold_count,
                (SELECT COUNT(*) FROM products_sum WHERE product_id = $product_id) as total_count
            FROM products_sum
                inner join `products` on `products`.`id` = `products_sum`.`product_id` 
                inner join `types` on `types`.`id` = `products`.`type_id`
            WHERE product_id = $product_id AND sold = 0
            GROUP BY product_id"));
    }

    public static function findProductsTotal($q, array $condition)
    {
        return DB::table('products_sum')
            ->select(DB::raw("COUNT(DISTINCT `product_id`) as count"))
            ->join('products', 'products.id', 'products_sum.product_id')
            ->where($condition)
            ->where('products.model', 'LIKE', "%$q%")
            ->orWhere('products.brand', 'LIKE', "%$q%")->first()->count;
    }

    public static function findDistinctProducts($q, array $condition)
    {
        return DB::table('products_sum')->select('product_id')
            ->join('products', 'products.id', 'products_sum.product_id')
            ->where($condition)
            ->where('products.model', 'LIKE', "%$q%")
            ->orWhere('products.brand', 'LIKE', "%$q%")
            ->distinct();
    }

}
