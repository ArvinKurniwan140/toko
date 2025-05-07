<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $premiumProducts = Product::where('is_premium', true)
            ->take(5)
            ->get();

        // Get products for the product section, organized by category
        $newArrivals = Product::where('category', 'new')
            ->take(8)
            ->get();

        $bestSellers = Product::where('category', 'best')
            ->take(8)
            ->get();

        // Get all products for the "recommended" filter
        $allProducts = Product::take(8)->get();

        return view('index', compact('premiumProducts', 'newArrivals', 'bestSellers', 'allProducts'));
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
    public function store(StoreProductRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
