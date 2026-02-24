<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cart/add/{product_id}/{quantity}',[CartController::class,'add'])->whereNumber('product_id')->whereNumber('quantity');
Route::get('/cart/remove/{product_id}/{quantity?}',[CartController::class,'remove'])->whereNumber('product_id')->whereNumber('quantity');
Route::get('total/amount',[CartController::class,'totalCartAmt']);
