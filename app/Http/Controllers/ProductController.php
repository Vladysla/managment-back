<?php

namespace App\Http\Controllers;

use App\ProductSum;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllAvailableProductsForPlace(Request $request)
    {
        $products = ProductSum::where('place_id', $request->user()->place->id)->where('sold', 0)->paginate(40);
        foreach ($products as $product) {
            $product->product;
            $product->color;
            $product->size;
            $product->place;
        }

        return response()->json($products);
    }

    public function getAllAvailableProducts()
    {
        $products = ProductSum::where('sold', 0)->paginate(40);
        foreach ($products as $product) {
            $product->product;
            $product->color;
            $product->size;
            $product->place;
        }

        return response()->json($products);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        $product->product->only(['id', 'brand', 'model']);
        $product->color;
        $product->size;
        $product->place;

        return response()->json($product->only(['id', 'sold', 'sold_at', 'product', 'color', 'size', 'place']));
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
