<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddToCart;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function add($product_id, $quantity){
        $validator = validator::make([
            'product_id' => $product_id,
            'quantity' => $quantity,
        ],
        [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ],
        [
            'product_id.required' => 'Product ID is required.',
                'product_id.integer'  => 'Product ID must be integer only.',
                'product_id.exists'   => 'Product not found in database.',

                'quantity.required'   => 'Quantity is required.',
                'quantity.integer'    => 'Quantity must be integer only.',
                'quantity.min'        => 'Quantity must be at least 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $cart = AddToCart::where('product_id',$product_id)->first();

        if($cart){
            //update product quantity
            $cart->quantity = $quantity;
            $cart->save();
        }
        else{
            //add new product
            $cart = AddToCart::create([
                'product_id' => $product_id,
                'quantity' => $quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully!',
            'data'  => $cart
        ],200);
    }

    public function remove($product_id, $quantity = null){
        $validator = validator::make([
            'product_id' => $product_id,
            'quantity' => $quantity,
        ],
        [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ],
        [
            'product_id.required' => 'Product ID is required.',
                'product_id.integer'  => 'Product ID must be integer only.',
                'product_id.exists'   => 'Product not found in database.',

                'quantity.required'   => 'Quantity is required.',
                'quantity.integer'    => 'Quantity must be integer only.',
                'quantity.min'        => 'Quantity must be at least 1.',
        ]);

        $cart = AddToCart::where('product_id',$product_id)->first();

        if(!$cart){
            return response()->json([
                'success' => false,
                'message' => 'Product not found in cart.',
                'data'  => $cart
            ],404);
        }
        if($quantity === null || $quantity >= $cart->quantity){
            $cart->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product remove from cart.',
            ]);
        }

        $cart->quantity -= $quantity;
        $cart->save();

        return response()->json([
           'success' => true,
            'message' => 'Product quantity decreased.',
            'data'  => $cart
        ]);
    }

    //display total amount
    public function totalCartAmt()
    {
        $cartItems = AddToCart::with('product')->get();

        $totalAmount = 0;

        foreach ($cartItems as $item) {
            if ($item->product) {
                $totalAmount += $item->quantity * $item->product->price;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Total Amount.',
            'total_items' => $cartItems->count(),
            'total_amount' => $totalAmount
        ]);
    }
}