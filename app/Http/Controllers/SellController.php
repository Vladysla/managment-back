<?php

namespace App\Http\Controllers;

use App\ProductSum;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Faker;

class SellController extends Controller
{
    private $pageNumber = 30;

    public function testDatabaseSell()
    {
//        $faker = Faker\Factory::create();
//        for ($i = 5213; $i < 12000; $i++) {
//            $product = ProductSum::find($i);
//            $product->sold = 1;
//            $product->sold_at = $faker->dateTimeBetween($startDate = '-10 month', $endDate = 'now');
//            $product->save();
//        }
    }

    public function sellProducts(Request $request)
    {
        foreach ($request->input('product_sum_ids') as $prod_id) {
            $product = ProductSum::find($prod_id);
            $product->sold = 1;
            $product->sold_at = Carbon::now();
            $product->save();
        }

        return response()->json($request->input('product_sum_ids'));
    }

    public function getSoldProductsPerDay(Request $request)
    {
        $options = [
            'sold' => 1,
            'place_id' => $request->user()->place_id
        ];

        if($request->input('show_places') == 'all' && $request->user()->role == 'admin') {
            $options = ['sold' => 1];
        }

        $order = 'sold_at';
        $order_direction = 'desc';
        if ($request->input('order')) {
            $order = $request->input('order');
        }
        if ($request->input('order_dir')) {
            $order_direction = $request->input('order_dir');
        }

        return ProductSum::select(DB::raw('SUM(products.price_sell) as sum_price, sum_id, places.name, places.id, sold_at'))
            ->where($options)
            ->join('products', 'products_sum.product_id', 'products.id')
            ->join('places', 'products_sum.place_id', 'places.id')
            ->groupBy(DB::raw('DATE(products_sum.sold_at)'))
            ->groupBy(DB::raw('products_sum.place_id'))
            ->orderBy($order, $order_direction)
            ->paginate($this->pageNumber);
    }

    public function getListHistoryByDate(Request $request, $date)
    {
        $options = [
            'sold' => 1
        ];

        if($request->input('place') && $request->user()->role == 'admin') {
            $options['place_id'] = $request->input('place');
        } else {
            $options['place_id'] = $request->user()->place_id;
        }

        $order = 'sold_at';
        $order_direction = 'desc';

        if ($request->input('order')) {
            $order = $request->input('order');
        }
        if ($request->input('order_dir')) {
            $order_direction = $request->input('order_dir');
        }

        if($q = $request->input('q')) {
            $products = ProductSum::with(['product.type', 'color', 'size'])
                ->where($options)
                ->where('products.model', 'LIKE', "%$q%")
                ->orWhere('products.brand', 'LIKE', "%$q%")
                ->join('products', 'products_sum.product_id', 'products.id')
                ->whereRaw("DATE(sold_at) = DATE('$date')")
                ->orderBy($order, $order_direction)
                ->paginate($this->pageNumber);
        } else {
            $products = ProductSum::with(['product.type', 'color', 'size'])
                ->where($options)
                ->join('products', 'products_sum.product_id', 'products.id')
                ->whereRaw("DATE(sold_at) = DATE('$date')")
                ->orderBy($order, $order_direction)
                ->paginate($this->pageNumber);
        }

        return $products;
    }

}
