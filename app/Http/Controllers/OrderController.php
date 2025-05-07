<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\OrderItem;
use App\Services\EncryptionService;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
        $this->middleware('auth');
    }
    public function index()
    {
        $userId = auth()->id();
        $orders = Order::where('user_id', $userId)->with('orderItems.product')->latest()->get();

        return view('order', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'zipcode' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'payment_method' => 'required|string|in:card,paypal',
        ]);

        $sessionId = session()->getId();
        $userId = auth()->id() ?? null;

        // Get cart items
        $cartItems = Cart::where('session_id', $sessionId)
            ->with('product')
            ->get();

        // Check if cart is empty
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty. Please add items before checkout.');
        }

        // Calculate totals
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $shipping = 0; // Default to free shipping
        $total_amount = $subtotal + $shipping;

        // Begin transaction
        try {
            DB::beginTransaction();

            // Create order - Model trait akan mengenkripsi atribut sesuai $encrypts
            $order = Order::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'company' => $request->company,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'zipcode' => $request->zipcode,
                'country' => $request->country,
                'phone' => $request->phone,
                'email' => $request->email,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total_amount' => $total_amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Jika terdapat data kartu kredit, enkripsi secara manual
            if ($request->has('card_number') && $request->filled('card_number')) {
                // Enkripsi data kartu kredit (tidak disimpan di database)
                $encryptedCardData = $this->encryptionService->encrypt([
                    'card_number' => $request->card_number,
                    'cvv' => $request->cvv,
                    'expiry' => $request->expiry
                ], 'payment_card');

                // Simpan metadata kartu terenkripsi ke dalam session untuk proses pembayaran
                // Hanya sementara, akan dihapus setelah pembayaran selesai
                session(['encrypted_payment_data' => $encryptedCardData]);
            }

            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'total' => $item->product->price * $item->quantity,
                ]);
            }

            // Clear cart after successful order creation
            Cart::where('session_id', $sessionId)->delete();

            // Clear sensitive data from session after processing
            if (session()->has('encrypted_payment_data')) {
                // Proses pembayaran di sini jika diperlukan
                // processPayment($order, session('encrypted_payment_data'));

                // Hapus data pembayaran dari session
                session()->forget('encrypted_payment_data');
            }

            DB::commit();

            // Redirect to order confirmation page
            return redirect()->route('order.confirmation', $order->id)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus data sensitif dari session jika terjadi kesalahan
            session()->forget('encrypted_payment_data');

            return redirect()->back()
                ->with('error', 'There was a problem processing your order. Please try again.')
                ->withInput($request->except(['card_number', 'cvv', 'expiry']));
        }
    }

    /**
     * Display order confirmation page.
     */
    public function confirmation($id)
    {
        $order = Order::with('orderItems.product')->findOrFail($id);

        // Simple security check - only the user who placed the order or admin can see it
        $sessionId = session()->getId();
        $userId = auth()->id() ?? null;

        // if ($order->session_id !== $sessionId && $order->user_id !== $userId && !auth()->user()?->isAdmin()) {
        //     abort(403);
        // }

        return view('confirmation', compact('order'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
