<?php

namespace App\Http\Controllers;

use App\ProductCheck;
use App\ProductSum;
use App\Type;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ProductCheckController extends Controller
{
    public function storeAll(Request $request)
    {
        $types = Type::all();
        $users = User::all();
        foreach ($users as $user) {
            foreach ($types as $type) {
                $countedProducts = ProductSum
                    ::join('products', 'products.id', 'products_sum.product_id')
                    ->where([
                        'products_sum.place_id' => $user->place_id,
                        'products.type_id' => $type->id,
                        'products_sum.sold' => 0
                    ])
                    ->count();
                ProductCheck::create([
                    'type_id' => $type->id,
                    'place_id'=> $user->place_id,
                    'count' => $countedProducts
                ]);
            }
        }
    }
}
