<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EncryptionService;

class CheckoutController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();

        $cart = \App\Models\Cart::where('session_id', $sessionId)
            ->with('product')
            ->get();

        $subtotal = $cart->sum(function ($item) {
            return optional($item->product)->price * $item->quantity;
        });

        $shipping = 0;
        $total = $subtotal + $shipping;

        return view('checkout', compact('cart', 'subtotal', 'shipping', 'total'));
    }


    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->middleware('auth');
        $this->encryptionService = $encryptionService;
    }


    public function processOrder(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'address' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'company' => 'nullable|string',
        ]);

        // Ambil cart dari database berdasarkan session_id
        $sessionId = session()->getId();
        $cartItems = \App\Models\Cart::where('session_id', $sessionId)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang belanja kosong.');
        }

        // Hitung subtotal, shipping, dan total
        $subtotal = $cartItems->sum(function ($item) {
            return optional($item->product)->price * $item->quantity;
        });

        $shipping = 0; // Bisa dimodifikasi sesuai logika pengiriman
        $total = $subtotal + $shipping;

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            // Buat order baru
            $order = Order::create([
                'user_id' => auth()->id(),
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
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Simpan item order
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'total' => $item->product->price * $item->quantity,
                ]);
            }

            // Hapus cart setelah order dibuat
            \App\Models\Cart::where('session_id', $sessionId)->delete();

            // Commit transaksi database
            DB::commit();

            // Log aktivitas order berhasil (tanpa data sensitif)
            Log::info('Order berhasil dibuat', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'timestamp' => now(),
            ]);

            // Redirect ke halaman terima kasih
            return redirect()->route('order.thankyou', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();

            // Log error
            Log::error('Error saat membuat order: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memproses pesanan Anda. Silakan coba lagi.')
                ->withInput();
        }
    }


    public function thankYou($orderId)
    {
        $order = Order::with('orderItems.product')->findOrFail($orderId);

        // Verifikasi bahwa order ini milik user yang sedang login
        if (auth()->id() !== $order->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('thankyou', compact('order'));
    }
}
