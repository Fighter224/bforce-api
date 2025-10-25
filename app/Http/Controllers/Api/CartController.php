<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cart->load('items.product');
        return response()->json($cart);
    }

    public function add(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $item = $cart->items()->where('product_id', $request->product_id)->first();
        if ($item) {
            $item->quantity += $request->quantity ?? 1;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1
            ]);
        }
        $cart->load('items.product');
        return response()->json($cart);
    }

    public function update(Request $request, $itemId)
    {
        $item = CartItem::findOrFail($itemId);
        $item->quantity = $request->quantity;
        $item->save();
        return response()->json($item);
    }

    public function remove($itemId)
    {
        $item = CartItem::findOrFail($itemId);
        $item->delete();
        return response()->json(['success' => true]);
    }

    public function clear()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $cart->items()->delete();
        return response()->json(['success' => true]);
    }
} 