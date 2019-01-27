<?php

namespace App\Http\Controllers;

use App\ProductSum;
use App\Product;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ProductController extends Controller
{

    private function getTransformedItems($array){
        $items = [];
        foreach ($array as $products) {
            foreach ($products as $product) {
                if(!array_key_exists($product->product_id, $items)) {
                    $items[$product->product_id] = [];
                }
                $items[$product->product_id]['info'] = [
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'price_arrival' => $product->price_arrival,
                    'price_sell' => $product->price_sell,
                ];
                if(!array_key_exists('sizes', $items[$product->product_id])) {
                    $items[$product->product_id]['sizes'] = [];
                }
                if(!array_key_exists($product->size_name, $items[$product->product_id]['sizes'])){
                    $items[$product->product_id]['sizes'][$product->size_name] = [];
                }
                if(!array_key_exists($product->color_name, $items[$product->product_id]['sizes'][$product->size_name])){
                    $items[$product->product_id]['sizes'][$product->size_name][$product->color_name] = $product->products_count;
                } else {
                    $items[$product->product_id]['sizes'][$product->size_name][$product->color_name] = $product->products_count;
                }
            }
        }

        return $items;
    }

    private function paginateProducts(int $perPage, Request $request, $place = false, $sold = '0'){

        $condition = function ($placeId, $sold) {
            if ($placeId) {
                return [
                    ['sold', '=', $sold],
                    ['place_id', '=', $placeId]
                ];
            }
            return [
                ['sold', '=', $sold]
            ];
        };

        $paginateTotal = DB::table('products_sum')
            ->select(DB::raw("COUNT(DISTINCT `product_id`) as count"))
            ->where($condition($place, $sold))->first()->count;
        $productsQuery = DB::table('products_sum')->select('product_id')->where($condition($place, $sold))->distinct();
        $agrigatesProducts = [];
        $page = $request->input('page') ?:1;
        if ($page) {
            $skip = $perPage * ($page - 1);
            $raw_query = $productsQuery->take($perPage)->skip($skip);
            foreach ($raw_query->get()->all() as $item){
                $agrigatesProducts[] = DB::table("products_sum")->select(DB::raw("product_id, brand, model, price_arrival, price_sell, sizes.name as size_name, colors.name as color_name, COUNT(*) as products_count"))
                    ->join('colors', 'colors.id', 'products_sum.color_id')
                    ->join('sizes', 'sizes.id', 'products_sum.size_id')
                    ->join('products', 'products.id', 'products_sum.product_id')
                    ->where('product_id', $item->product_id)
                    ->where('sold', $sold)
                    ->groupBy('product_id', 'sizes.name', 'colors.name')->get();
            }
        }

        $items = $this->getTransformedItems($agrigatesProducts);

        return new Paginator($items, $paginateTotal, 2, $request->page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAvailableProductsForPlace(Request $request)
    {
        $products = $this->paginateProducts(2, $request, $request->user()->place->id);

        return response()->json($products);
    }


    public function getAllAvailableProducts(Request $request)
    {

        $products = $this->paginateProducts(2, $request);

        return response()->json($products);

    }


    /**
     * Create Products
     *
     * @param Request $request
     * $request->data = {
            "color_id": {
                "size_id": 3,
                "size_id": 2
            },
            "color_id": {
                "size_id": 1
            }
        }
     */
    public function storeProduct(Request $request)
    {
        try {
            $product_id = 0;

            if($request->product_isset) {
                $product = Product::find($request->product_id);
                if($product) {
                    $product_id = $product->id;
                } else {
                    return $this->showMessage('Product not found!', 403);
                }
            } else {
                $newProduct = Product::create($request->all());
                if($newProduct) {
                    $product_id = $newProduct->id;
                } else {
                    return $this->showMessage('Product create error!', 500);
                }
            }

            foreach ($request->data as $color => $sizes) {
                foreach ($sizes as $size => $count) {
                    for ($i = 0; $i < $count; $i++) {
                        ProductSum::create([
                            'product_id' => $product_id,
                            'color_id'   => $color,
                            'size_id'    => $size,
                            'place_id'   => $request->place_id
                        ]);
                    }
                }
            }

            return $this->showMessage('Success!', 200);

        } catch (\Exception $exception) {
            return $this->showMessage('Error on server', 500);
        }
    }

    /**
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    private function showMessage(string $message, int $status)
    {
        return response()->json([
            'message' => $message
        ],$status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = ProductSum::find((int) $id);
        $product->product;
        $product->color;
        $product->size;
        $product->place;
        $product->type;

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
