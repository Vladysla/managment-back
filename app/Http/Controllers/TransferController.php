<?php

namespace App\Http\Controllers;

use App\Transfer;
use App\ProductSum;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    private function showMessage(string $message, int $status)
    {
        return response()->json([
            'message' => $message
        ],$status);
    }

    public function transferProducts(Request $request)
    {
        $transferProducts = [];
        foreach ($request->input('product_sum_ids') as $product_id) {
            $transfer = new Transfer;
            $transfer->product_id = $product_id;
            $transfer->from_place = $request->input('place_from');
            $transfer->to_place = $request->input('place_to');
            $transfer->status = 0;
            $transfer->save();
            $transferProducts[] = $transfer;
        }

        return response()->json([
            'transferred' => $transferProducts
        ], 200);
    }

    public function applyTransfer(Request $request)
    {
        $transferredProduct = Transfer::find($request->input('transfer_id'));
        $transferredProduct->status = 1;
        $transferredProduct->save();

        $productSum = ProductSum::find($transferredProduct->product_id);

        $productSum->place_id = $transferredProduct->to_place;
        $productSum->save();

        return response()->json([
            'id' => $productSum->sum_id
        ], 200);
    }

    public function cancelTransfer(Request $request)
    {
        $transferredProduct = Transfer::find($request->input('transfer_id'));
        $transferredProduct->delete();

        return response()->json([
            'id' => $request->input('transfer_id')
        ], 200);
    }

    public function getListMyIncomeProducts(Request $request)
    {
        $transferredProducts = Transfer::with(['product_sum', 'from_place', 'to_place'])
            ->where('from_place', $request->user()->place_id)
            ->orWhere('to_place', $request->user()->place_id)
            ->paginate(2);
        return response()->json($transferredProducts, 200);
    }
}
