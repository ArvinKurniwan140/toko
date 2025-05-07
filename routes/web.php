<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ProductController::class, 'index']);

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);


Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Route::get('/regis', function () {
//     return view('register');
// });



Route::get('cart', [CartController::class, 'index'])->name('cart');
Route::put('/cart/update/{cart}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cart}', [CartController::class, 'destroy'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/confirmation/{order}', [OrderController::class, 'confirmation'])->name('order.confirmation');

// Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
// Route::post('/checkout/process', [CheckoutController::class, 'processOrder'])->name('checkout.process');
Route::get('/order/thankyou/{orderId}', [CheckoutController::class, 'thankYou'])->name('order.thankyou');

Route::get('/register', [RegisterController::class, 'index']);
Route::post('/register', [RegisterController::class, 'register']);


Route::middleware('auth')->group(function () {
    Route::post('/cart/add/{product}', [CartController::class, 'store'])->name('cart.add');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'processOrder'])->name('checkout.process');
    Route::get('/account/orders', [OrderController::class, 'index'])->name('account.orders');
});
