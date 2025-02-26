<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function show()
    {
        $cart = Cart::with('items.product')->where('user_id', Auth::id())->first();
        
        if(!$cart){
            return response()->json(['message' => 'cart is empty'],404);
        }
        return response()->json($cart);
    }
    
    // Add an item to the cart
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Find or create cart
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        // Check if item already exists in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json(['message' => 'Item added to cart']);
    }

    // Update cart item quantity
    public function updateCartItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::findOrFail($itemId);
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated']);
    }

    // Remove item from cart
    public function removeCartItem($itemId)
    {
        CartItem::findOrFail($itemId)->delete();
        return response()->json(['message' => 'Item removed']);
    }

    // Clear entire cart
    public function clearCart()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json(['message' => 'Cart cleared']);
    }
}
