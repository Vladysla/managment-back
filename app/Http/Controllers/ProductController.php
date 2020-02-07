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
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private $pageNumber = 30;

    public function testDatabase()
    {
//        for ($i = 0; $i < 500; $i++) {
//            $product = factory(Product::class)->create();
//        }
//        for ($i = 0; $i < 10000; $i++) {
//            $product = factory(ProductSum::class)->create();
//        }
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
                        'type' => $product->type_name,
                        'photo' => $product->photo
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
                        'type' => $product->type_name,
                        'photo' => $product->photo
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

    private function paginateProducts(int $perPage, Request $request, $place = "ALL", $type = "ALL", $sold = "NO", $order = "", $orderDir = ""){

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
            $productsQuery = ProductSum::getDistinctProducts($condition($place, $type, $sold), $order, $orderDir);
        }
        $agrigatesProducts = [];
        $page = $request->input('page') ?:1;
        if ($page) {
            $skip = $perPage * ($page - 1);
            $raw_query = $productsQuery->take($perPage)->skip($skip);
            foreach ($raw_query->get()->all() as $item){
                $agrigatesProducts[] = ProductSum::getProductInfo($item->product_id, $sold)[0];
            }
        }
        //$items = $this->getTransformedItems($agrigatesProducts, $sold);

        return new Paginator($agrigatesProducts, $paginateTotal, $perPage, $request->page, [
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
        $products = $this->paginateProducts($this->pageNumber, $request, $request->user()->place->id);

        return response()->json($products);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAvailableProducts(Request $request)
    {

        /*if($request->input('type_id') && $request->input('place_id')) {
            return $this->getAvailableProductsForPlaceType($request, $request->input('place_id'), $request->input('type_id'));
        }


        if($request->input('type_id')) {
            return $this->getAvailableProductsForPlaceType($request, "ALL", $request->input('type_id'));
        }*/

        if($request->input('order') && $request->input('order_dir') || $request->input('place_id') || $request->input('type_id')) {
            return $this->getAvailableProductsForPlaceType($request, $request->input('place_id'), $request->input('type_id'), $request->input('order'), $request->input('order_dir'));
        }

        $products = $this->paginateProducts($this->pageNumber, $request);

        return response()->json($products);

    }

    private function getAvailableProductsForPlaceType(Request $request, $place_id, $type_id, $order = '', $order_dir = "")
    {
        $products = $this->paginateProducts($this->pageNumber, $request, $place_id, $type_id, "NO", $order, $order_dir);
        return response()->json($products);
    }

    public function getSoldProducts(Request $request)
    {
        if($request->input('place_id')) {
            $products = $this->paginateProducts($this->pageNumber, $request, $place = $request->input('place_id'), $type = "ALL", $sold = "1");
            return response()->json($products);
        }

        $products = $this->paginateProducts($this->pageNumber, $request, $place = "ALL", $type = "ALL", $sold = "1");
        return response()->json($products);
    }

    public function getProductInfo(Request $request, $product_id)
    {
        if ($product_id) {
            $products = ProductSum::getProductFullInfo($product_id, 0);
            $agrigate[] = $products->all();
            $items = $this->getTransformedItems($agrigate, 0);
            return response()->json($items);
        }

        return $this->showMessage('Product not found!', 403);
    }



    /**
     * Create Products
     *
     * @param Request $request
     * $request->data = {
     * "color_name": {
     * "size_name": 3,
     * "size_name": 2
     * },
     * "color_name": {
     * "size_name": 1
     * }
     * }
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeProduct(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                foreach (json_decode($request->product_color_size) as $color => $sizes) {
                    foreach ($sizes as $size => $count) {
                        if (!is_numeric($count)) {
                            return $this->showMessage('Not numeric!', 400);
                        }
                    }
                }
                if($request->product_exist != 0) {
                    $product = Product::find(json_decode($request->productSelected)->id);
                    if($product) {
                        $product_id = $product->id;
                    } else {
                        return $this->showMessage('Product not found!', 403);
                    }
                } else {
                    $productPhoto = 'no-photo.jpg';
                    $newProduct = new Product;
                    $newProduct->brand = $request->brand;
                    $newProduct->model = $request->model;
                    $newProduct->price_arrival = $request->price_arrival;
                    $newProduct->price_sell = $request->price_sell;
                    $newProduct->type_id = $request->type_id;
                    if ($request->hasFile('product_photo')) {
                        $extension = $request->file('product_photo')->getClientOriginalExtension();
                        $filenameStore = Str::random(8) . time() . '.' . $extension;
                        $request->file('product_photo')->storeAs('images', $filenameStore);
                        $img = Image::make(public_path("uploads/images/$filenameStore"))->resize(450, 450);
                        $img->save(public_path("uploads/images/$filenameStore"));
                        $productPhoto = $filenameStore;
                    }
                    $newProduct->photo = $productPhoto;
                    $newProduct->save();
                    if($newProduct) {
                        $product_id = $newProduct->id;
                    } else {
                        return $this->showMessage('Product create error!', 400);
                    }
                }
                foreach (json_decode($request->product_color_size) as $color => $sizes) {
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
            }, 2);

        } catch (\Exception $exception) {
            return $this->showMessage('Error on server', 400);
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
        $types = Type::all();
        return response()->json($types);
    }

    public function getColors()
    {
        $colors = Color::all();
        return response()->json($colors);
    }

    public function getSizes()
    {
        $sizes = Size::all();
        return response()->json($sizes);
    }

    public function getAllModels()
    {
        $products = Product::with('type')->get(['model', 'brand', 'id', 'photo', 'type_id', 'price_arrival', 'price_sell']);
        return response()->json($products);
    }

    public function getCurrency()
    {
        try {
            $bank_page = file_get_contents('https://minfin.com.ua/currency/');
        } catch (\Exception $exception) {
            $bank_page = '';
        }
        preg_match('/<span class=\\"mfcur-nbu-full-wrap\\">\\n([\d]+.[\d]+)</', $bank_page, $match);
        $reslut = ($match && is_array($match)) ? $match[1] : '0';

        return response()->json($reslut);
    }

    public function getSeparatedProductsForPlace(Request $request)
    {
        $condition = function ($sold, $place_id, $type_id, $order, $order_dir) {
            $options = [];
            array_push($options, ['sold', '=', $sold]);
            array_push($options, ['place_id', '=', $place_id]);
            $type_id && array_push($options, ['type_id', '=', $type_id]);
            $order && $options['order'] = $order;
            $order_dir && $options['order_dir'] = $order_dir;

            return $options;
        };

        if ($request->input('q')) {
            $products = ProductSum::findListAvailableProducts($condition($request->input('sold'), $request->user()->place->id, $request->input('type_id'), $request->input('order'), $request->input('order_dir')), $request->input('q'));
        } else {
            $products = ProductSum::getListAvailableProducts($condition($request->input('sold'), $request->user()->place->id, $request->input('type_id'), $request->input('order'), $request->input('order_dir')));
        }

        return response()->json($products);
    }
}
