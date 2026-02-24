<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddToCart;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    //Add New Product with validations
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

        //Errors 
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $cart = AddToCart::where('product_id',$product_id)->first();

        if($cart){
            //If Product is already in Cart -> update product quantity
            $cart->quantity += $quantity;
            $cart->save();
        }
        else{
            //Otherwise Add New Product
            $cart = AddToCart::create([
                'product_id' => $product_id,
                'quantity' => $quantity,
            ]);
        }

        //Return JSON Response
        return response()->json([
            'success' => true,
            'message' => 'Product added successfully!',
            'data'  => $cart
        ],200);
    }

    //Remove Product Or Quantity of Product
    public function remove($product_id, $quantity = null){
        $validator = validator::make(
        [
            'product_id' => $product_id,
            'quantity' => $quantity,
        ],
        [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ],
        [
            'product_id.required' => 'Product ID is required.',
            'product_id.integer' => 'Product ID must be integer only.',
            'product_id.exists' => 'Product not found in database.',

            'quantity.integer' => 'Quantity must be integer only.',
            'quantity.min' => 'Quantity must be at least 1.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cart = AddToCart::where('product_id',$product_id)->first();

        //Product not found in cart.
        if(!$cart){
            return response()->json([
                'success' => false,
                'message' => 'Product not found in cart.',
                'data'  => $cart
            ],404);
        }

        //Product remove from cart.
        if($quantity === null){
            $cart->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product remove from cart.',
            ]);
        }

        if ($quantity > $cart->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'Remove quantity exceeds cart quantity.'
            ], 400);
        }

        $newQty = $cart->quantity - $quantity;
        if($newQty == 0){
            $cart->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product remove from cart.',
            ]);
        }

        //quantity decreased 
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

        //Calculate the totalAmount = qty * price
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