<?php

namespace App\Http\Controllers;

use App\ProductSum;
use App\Product;
use DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAvailableProductsForPlace(Request $request)
    {
        $products = ProductSum::where('place_id', $request->user()->place->id)->where('sold', 0)->with('product', 'color', 'size', 'place', 'type')->paginate(10);

        return response()->json($products);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAvailableProducts()
    {
        //$products = ProductSum::where('sold', 0)->with('product', 'color', 'size', 'place', 'type')->groupBy('product_id')->paginate(10);
        //$products = \DB::table('products_sum');
        $products = DB::select(DB::raw('SELECT product_id, sizes.name as size_name, colors.name as color_name, COUNT(*) as products_count 
                                                FROM products_sum 
                                                JOIN colors ON colors.id = products_sum.color_id 
                                                JOIN sizes ON sizes.id = products_sum.size_id 
                                                GROUP BY product_id, sizes.name, colors.name'));
        $newProd = DB::table("products_sum")->select(DB::raw("product_id, sizes.name as size_name, colors.name as color_name, COUNT(*) as products_count"))
                        ->join('colors', 'colors.id', 'products_sum.color_id')
                        ->join('sizes', 'sizes.id', 'products_sum.size_id')
                        ->groupBy('product_id', 'sizes.name', 'colors.name')->paginate(3   );
        //dd($products);

        $items = [];
        foreach ($newProd->items() as $product) {
            if(!array_key_exists($product->product_id, $items)) {
                $items[$product->product_id] = [];
            }
            if(!array_key_exists($product->size_name, $items[$product->product_id])){
                $items[$product->product_id][$product->size_name] = [];
            }
            if(!array_key_exists($product->color_name, $items[$product->product_id][$product->size_name])){
                $items[$product->product_id][$product->size_name][$product->color_name] = $product->products_count;
            } else {
                $items[$product->product_id][$product->size_name][$product->color_name] = $product->products_count;
            }
        }
      //  dd($newProd->items());

        $data = [
            'items' => $items,
            'lastPage' => $newProd->lastPage(),
            'currentPage' => $newProd->currentPage()
        ];
        $collective = collect();

        dd($data);
        //return response()->json($obj);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
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
        $product_id = 0;

        if($request->product_isset) {
            $product = Product::find($request->product_id);
            if($product) {
                $product_id = $product->id;
            }
        } else {
            $newProduct = Product::create($request->all());
            if($newProduct) {
                $product_id = $newProduct->id;
            }
        }

        foreach ($request->data as $color => $sizes) {
            foreach ($sizes as $size => $count) {
                for ($i = 0; $i < $count; $i++) {
                    ProductSum::create([
                        'product_id' => $product_id,
                        'color_id'   => $color,
                        'size_id'    => $size,
                        'place_id'   => $request->place_id,
                        'type_id'    => $request->type_id
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Error on the server'
        ], 400);
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
