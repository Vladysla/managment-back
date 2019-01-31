<?php

namespace App\Http\Controllers;

use App\Color;
use App\Place;
use App\ProductSum;
use App\Product;
use App\Size;
use App\Type;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ProductController extends Controller
{

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

    private function getTransformedItems($array, $sold){
        $items = [];
        if($sold == "NO") {
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
                        'sold_at' => $product->sold_at,
                        'type' => $product->type_name
                    ];
                    if(!array_key_exists('places', $items[$product->product_id])) {
                        $items[$product->product_id]['places'] = [];
                    }
                    if(!array_key_exists($product->place_name, $items[$product->product_id]['places'])) {
                        $items[$product->product_id]['places'][$product->place_name] = [];
                    }
                    if(!array_key_exists($product->size_name, $items[$product->product_id]['places'][$product->place_name])){
                        $items[$product->product_id]['places'][$product->place_name][$product->size_name] = [];
                    }
                    if(!array_key_exists($product->color_name, $items[$product->product_id]['places'][$product->place_name][$product->size_name])){
                        $items[$product->product_id]['places'][$product->place_name][$product->size_name][$product->color_name] = $product->products_count;
                    } else {
                        $items[$product->product_id]['places'][$product->place_name][$product->size_name][$product->color_name] = $product->products_count;
                    }
                }
            }
        } else {
            foreach ($array as $products) {
                foreach ($products as $product) {
                    $items[$product->sold_at]['info'] = [
                        'brand' => $product->brand,
                        'model' => $product->model,
                        'price_arrival' => $product->price_arrival,
                        'price_sell' => $product->price_sell,
                        'sold_at' => $product->sold_at,
                        'type' => $product->type_name
                    ];
                    if(!array_key_exists('sizes', $items[$product->sold_at])) {
                        $items[$product->sold_at]['sizes'] = [];
                    }
                    if(!array_key_exists($product->size_name, $items[$product->sold_at]['sizes'])){
                        $items[$product->sold_at]['sizes'][$product->size_name] = [];
                    }
                    if(!array_key_exists($product->color_name, $items[$product->sold_at]['sizes'][$product->size_name])){
                        $items[$product->sold_at]['sizes'][$product->size_name][$product->color_name] = $product->products_count;
                    } else {
                        $items[$product->sold_at]['sizes'][$product->size_name][$product->color_name] = $product->products_count;
                    }
                }
            }
        }

        return $items;
    }

    private function paginateProducts(int $perPage, Request $request, $place = "ALL", $type = "ALL", $sold = "NO"){

        $condition = function ($placeId, $typeId, $sold) {
            $options = [];
            if($sold) {
                $sold == "NO" ? array_push($options, ['sold', '=', '0']) : array_push($options, ['sold', '=', '1']);
            }
            if ($placeId) {
                $placeId != "ALL" && array_push($options, ['place_id', '=', $placeId]);
            }
            if ($typeId) {
                $typeId != "ALL" && array_push($options, ['products.type_id', '=', $typeId]);
            }
            return $options;
        };

        if($request->input('q')){
            $paginateTotal = ProductSum::findProductsTotal($request->input('q'), $condition($place, $type, $sold));
            $productsQuery = ProductSum::findDistinctProducts($request->input('q'),$condition($place, $type, $sold));
        } else {
            $paginateTotal = ProductSum::getTotalProducts($condition($place, $type, $sold));
            $productsQuery = ProductSum::getDistinctProducts($condition($place, $type, $sold));
        }
        $agrigatesProducts = [];
        $page = $request->input('page') ?:1;
        if ($page) {
            $skip = $perPage * ($page - 1);
            $raw_query = $productsQuery->take($perPage)->skip($skip);
            foreach ($raw_query->get()->all() as $item){
                $agrigatesProducts[] = ProductSum::getProductInfo($item->product_id, $sold);
            }
        }
        $items = $this->getTransformedItems($agrigatesProducts, $sold);

        return new Paginator($items, $paginateTotal, $perPage, $request->page, [
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


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAvailableProducts(Request $request)
    {

        if($request->input('type_id') && $request->input('place_id')) {
            return $this->getAvailableProductsForPlaceType($request, $request->input('place_id'), $request->input('type_id'));
        }

        if($request->input('place_id')) {
            return $this->getAvailableProductsForPlaceType($request, $request->input('place_id'), "ALL");
        }

        if($request->input('type_id')) {
            return $this->getAvailableProductsForPlaceType($request, "ALL", $request->input('type_id'));
        }

        $products = $this->paginateProducts(2, $request);

        return response()->json($products);

    }

    private function getAvailableProductsForPlaceType(Request $request, $place_id, $type_id)
    {
        $products = $this->paginateProducts(2, $request, $place_id, $type_id);
        return response()->json($products);
    }

    public function getSoldProducts(Request $request)
    {
        if($request->input('place_id')) {
            $products = $this->paginateProducts(2, $request, $place = $request->input('place_id'), $type = "ALL", $sold = "1");
            return response()->json($products);
        }

        $products = $this->paginateProducts(2, $request, $place = "ALL", $type = "ALL", $sold = "1");
        return response()->json($products);
    }



    /**
     * Create Products
     *
     * @param Request $request
     * $request->data = {
     * "color_id": {
     * "size_id": 3,
     * "size_id": 2
     * },
     * "color_id": {
     * "size_id": 1
     * }
     * }
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProduct(Request $request)
    {
        try {

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

    public function getPlaces()
    {
        $places = Place::all();
        return response()->json($places);
    }

    public function getTypes()
    {
        $places = Type::all();
        return response()->json($places);
    }

    public function getColors()
    {
        $places = Color::all();
        return response()->json($places);
    }

    public function getSizes()
    {
        $places = Size::all();
        return response()->json($places);
    }
}
