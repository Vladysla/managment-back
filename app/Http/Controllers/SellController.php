<?php

namespace App\Http\Controllers;

use App\ProductSum;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class SellController extends Controller
{
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

        return ProductSum::select(DB::raw('SUM(products.price_sell) as sum_price, sum_id, places.name, sold_at'))
            ->where($options)
            ->join('products', 'products_sum.product_id', 'products.id')
            ->join('places', 'products_sum.place_id', 'places.id')
            ->groupBy(DB::raw('DATE(products_sum.sold_at)'))
            ->groupBy(DB::raw('products_sum.place_id'))
            ->orderBy($order, $order_direction)
            ->paginate(2);
    }

    public function getListHistoryByDate(Request $request, $date, $place = null)
    {
        $options = [
            'sold' => 1
        ];
        if($place && $request->user()->role == 'admin') {
            $options['place_id'] = $place;
        }

        return ProductSum::with(['product.type', 'color', 'size'])
            ->where($options)
            ->whereRaw("DATE(sold_at) = DATE('$date')")
            ->paginate(2);
    }

}
