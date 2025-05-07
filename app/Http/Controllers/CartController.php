<?php

namespace App\Http\Controllers;

use App\Models\Cart; // Perhatikan nama model dengan huruf kapital di awal
use App\Http\Requests\StorecartRequest;
use App\Http\Requests\UpdatecartRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessionId = session()->getId();
        $cart = Cart::where('session_id', $sessionId)
            ->with('product')
            ->get();

        $subtotal = $cart->count() > 0 ? $cart->sum(function ($item) {
            return $item->product->price * $item->quantity;
        }) : 0;


        return view('cart', compact('cart', 'subtotal'));
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($id)
    {
        $product = Product::findOrFail($id);
        $sessionId = session()->getId();

        $cart = Cart::where('session_id', $sessionId)
            ->where('product_id', $id)
            ->first();

        if ($cart) {
            $cart->quantity += $request->quantity ?? 1;
            $cart->save();
        } else {
            Cart::create([
                'session_id' => $sessionId,
                'product_id' => $id,
                'quantity' => $request->quantity ?? 1
            ]);
        }

        return redirect()->route('cart')->with('success', 'Product added to cart!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->quantity = $request->quantity;
        $cart->save();

        return redirect()->route('cart')->with('success', 'Cart updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        return redirect()->route('cart')->with('success', 'Item removed from cart!');
    }

    /**
     * Clear all cart items for the current session.
     */
    public function clear()
    {
        $sessionId = session()->getId();
        Cart::where('session_id', $sessionId)->delete();

        return redirect()->route('cart')->with('success', 'Cart cleared!');
    }

    /**
     * Show checkout page.
     */
    public function checkout()
    {

        $sessionId = session()->getId();


        // Get fresh cart data to ensure we have the latest information
        $cart = Cart::where('session_id', $sessionId)
            ->with('product')
            ->get();

        // Check if cart is empty
        if ($cart->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty. Please add items before checkout.');
        }

        // Calculate subtotal with fresh data
        $subtotal = $cart->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        // Calculate shipping cost (if applicable)
        $shipping = 0; // Default to free shipping or adjust as needed

        // Calculate total
        $total = $subtotal + $shipping;

        return view('checkout', compact('cart', 'subtotal', 'shipping', 'total'));
    }
}
