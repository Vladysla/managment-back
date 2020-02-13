<?php

namespace App\Http\Controllers;

use App\Transfer;
use App\ProductSum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    private $pageNumber = 30;

    private function showMessage(string $message, int $status)
    {
        return response()->json([
            'message' => $message
        ],$status);
    }

    public function transferProducts(Request $request)
    {
        $res = DB::transaction(function () use ($request) {
            $transferProducts = [];
            foreach ($request->input('product_sum_ids') as $product_id) {
                $findTransfer = Transfer::where([
                    ['product_id', $product_id],
                    ['from_place', $request->input('place_from')],
                    ['to_place', $request->input('place_to')],
                    ['status', '0']
                ])->first();
                $findProductId = ProductSum::find($product_id)->place_id;
                if(($findTransfer == null) && ($findProductId === $request->input('place_from'))) {
                    $transfer = new Transfer;
                    $transfer->product_id = $product_id;
                    $transfer->from_place = $request->input('place_from');
                    $transfer->to_place = $request->input('place_to');
                    $transfer->status = 0;
                    $transfer->save();
                    array_push($transferProducts, $transfer->id);
                }
            }
            return $transferProducts;
        }, 2);

        return response()->json([
            'transferred' => $res
        ], 200);
    }

    public function applyTransfer(Request $request)
    {
        $transferredProduct = Transfer::find($request->input('transfer_id'));
        if ($transferredProduct == null || $transferredProduct->status == 1) {
            return response()->json([
                'message' => 'Этот запрос уже был обработан!'
            ]);
        }
        $transferredProduct->status = 1;
        $transferredProduct->save();
        $productSum = ProductSum::find($transferredProduct->product_id);

        $productSum->place_id = $transferredProduct->to_place;
        $productSum->save();

        return response()->json([
            'id' => $transferredProduct->id
        ], 200);
    }

    public function cancelTransfer(Request $request)
    {
        $transferredProduct = Transfer::find($request->input('transfer_id'));
        if ($transferredProduct == null || $transferredProduct->status == 1) {
            return response()->json([
                'message' => 'Этот запрос уже был обработан!'
            ]);
        }
        $transferredProduct->delete();

        return response()->json([
            'id' => $request->input('transfer_id')
        ], 200);
    }

    public function getListMyIncomeProducts(Request $request)
    {
        $transferredProducts = Transfer::with(['product_sum_transfer', 'from_place', 'to_place'])
            ->where('status', '0')
            ->where('to_place', $request->user()->place_id)
            ->paginate($this->pageNumber);
        return response()->json($transferredProducts, 200);
    }

    public function getListMyHistory(Request $request)
    {
        $order = 'created_at';
        $order_direction = 'desc';

        if ($request->input('order')) {
            $order = $request->input('order');
        }
        if ($request->input('order_dir')) {
            $order_direction = $request->input('order_dir');
        }

        $transferredProducts = Transfer::with(['product_sum_transfer', 'from_place', 'to_place'])
            ->where('from_place', $request->user()->place_id)
            ->orWhere('to_place', $request->user()->place_id)
            ->orderBy($order, $order_direction)
            ->paginate($this->pageNumber);
        return response()->json($transferredProducts, 200);
    }

    public function getTotalIncomeProducts(Request $request)
    {
        $place = $request->input('place_id');
        $count = Transfer::countedIncomeProducts($place);

        return response()->json($count, 200);
    }

    public function applyAll(Request $request)
    {
        $ids = DB::transaction(function () use ($request) {
            $place_id = $request->user()->place_id;
            $ids = Transfer::getPendingIds($place_id)->pluck('product_id')->toArray();
            Transfer::applyAll($place_id);
            $products = DB::table('products_sum')->whereIn('sum_id', $ids)->update(['place_id' => $place_id]);
            return $products;
        });
        return response()->json($ids);
    }

    public function cancelAll(Request $request)
    {
        $place_id = $request->user()->place_id;
        $query = Transfer::where(['to_place' => $place_id, 'status' => 0])->delete();

        return response()->json($query);
    }
}
