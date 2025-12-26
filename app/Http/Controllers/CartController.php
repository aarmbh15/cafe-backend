<?php

// namespace App\Http\Controllers;

// use App\Models\Cart;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class CartController extends Controller
// {
//     public function count()
//     {
//         $count = Cart::where('user_id', Auth::id())
//                      ->sum('quantity');

//         return response()->json(['count' => (int) $count]);
//     }

//     public function add(Request $request)
//     {
//         $request->validate([
//             'product_id' => 'required|exists:products,id',
//             'quantity'   => 'required|integer|min:1',
//             'variant_id' => 'nullable|exists:product_variants,id',
//         ]);

//         $userId     = Auth::id();
//         $productId  = $request->product_id;
//         $variantId  = $request->variant_id;
//         $addQty     = $request->quantity;

//         // Start transaction to ensure atomicity
//         \DB::transaction(function () use ($userId, $productId, $variantId, $addQty, &$cartItem) {
//             // Lock the variant row to prevent race conditions
//             $variant = null;
//             if ($variantId) {
//                 $variant = \App\Models\ProductVariant::where('id', $variantId)
//                     ->lockForUpdate()
//                     ->firstOrFail();

//                 if ($variant->stock_quantity < $addQty) {
//                     throw new \Exception('Not enough stock available');
//                 }

//                 // Decrease stock
//                 $variant->decrement('stock_quantity', $addQty);
//             }

//             // Now handle cart
//             $cartItem = Cart::where([
//                 'user_id'    => $userId,
//                 'product_id' => $productId,
//                 'variant_id' => $variantId ?? null,
//             ])->first();

//             if ($cartItem) {
//                 $cartItem->increment('quantity', $addQty);
//             } else {
//                 $cartItem = Cart::create([
//                     'user_id'    => $userId,
//                     'product_id' => $productId,
//                     'variant_id' => $variantId,
//                     'quantity'   => $addQty,
//                 ]);
//             }
//         });

//         return response()->json([
//             'message' => 'Added to cart successfully',
//         ]);
//     }
    
//     // public function index()
//     // {
//     //     try {
//     //         $items = Cart::with(['product.category', 'variant'])
//     //             ->where('user_id', Auth::id())
//     //             ->get();

//     //         $cartData = $items->map(function ($cartItem) {
//     //             // Safely get product (force load if missing)
//     //             $product = $cartItem->product;
//     //             if (!$product) {
//     //                 // Fallback: reload if relationship failed
//     //                 $product = \App\Models\Product::find($cartItem->product_id);
//     //             }

//     //             $title = $product?->name ?? 'Unknown Product';

//     //             $variant = $cartItem->variant;

//     //             // Variant details: "Navy • L"
//     //             $variantDetails = null;
//     //             if ($variant && $variant->attributes) {
//     //                 $attrs = is_string($variant->attributes) 
//     //                     ? json_decode($variant->attributes, true) 
//     //                     : $variant->attributes;

//     //                 if (is_array($attrs)) {
//     //                     $variantDetails = collect($attrs)->values()->join(' • ');
//     //                 }
//     //             }

//     //             // Image priority: variant image → product image → placeholder
//     //             $image = $product?->image_url ?? 'https://via.placeholder.com/150';

//     //             // Category name
//     //             $categoryName = $product?->category?->name ?? 'Uncategorized';

//     //             return [
//     //                 'id'              => $cartItem->id,
//     //                 'title'           => $title,
//     //                 'variant_details' => $variantDetails,
//     //                 'image'           => $image,
//     //                 'category'        => (object)['name' => $categoryName],
//     //                 'price'           => $cartItem->price,
//     //                 'quantity'        => $cartItem->quantity,
//     //             ];
//     //         });

//     //         return response()->json([
//     //             'cart'       => $cartData,
//     //             'totalPrice' => $items->sum('subtotal'),
//     //             'totalItems' => $items->sum('quantity'),
//     //         ]);
//     //     } catch (\Exception $e) {
//     //         \Log::error('Cart index error: ' . $e->getMessage());
//     //         return response()->json(['error' => 'Failed to load cart'], 500);
//     //     }
//     // }
//     public function index()
//     {
//         try {
//             $items = Cart::where('user_id', Auth::id())
//                 ->orderBy('created_at', 'asc')
//                 ->get();

//             $cartData = $items->map(function ($cartItem) {
//                 // Extract selections – ensure it's always an array
//                 $selections = is_array($cartItem->selections) 
//                     ? $cartItem->selections 
//                     : json_decode($cartItem->selections, true) ?? [];

//                 // Ensure each selection has at least a "name" key
//                 $selections = collect($selections)->map(function ($sel) {
//                     if (is_string($sel)) {
//                         return ['name' => $sel];
//                     }
//                     if (is_array($sel) && isset($sel['name'])) {
//                         return ['name' => $sel['name']];
//                     }
//                     return ['name' => 'Unknown option'];
//                 })->toArray();

//                 return [
//                     'id'          => $cartItem->id,
//                     'base'        => $cartItem->item_name,
//                     'selections'  => $selections,
//                     'totalPrice'  => (float) $cartItem->total_price,   // price per unit (including options)
//                     'quantity'    => $cartItem->quantity,
//                 ];
//             });

//             // Calculate total (sum of total_price * quantity for all items)
//             $totalAmount = $items->sum(function ($item) {
//                 return $item->total_price * $item->quantity;
//             });

//             return response()->json([
//                 'cart'  => $cartData,
//                 'total' => number_format($totalAmount, 2),  // e.g., "24.97" → matches £{total} in JSX
//             ]);
//         } catch (\Exception $e) {
//             \Log::error('Cart index error: ' . $e->getMessage());
//             return response()->json(['error' => 'Failed to load cart'], 500);
//         }
//     }

//     // public function remove($id)
//     // {
//     //     \DB::transaction(function () use ($id) {
//     //         $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

//     //         // Return stock
//     //         if ($cartItem->variant_id) {
//     //             \App\Models\ProductVariant::where('id', $cartItem->variant_id)
//     //                 ->increment('stock_quantity', $cartItem->quantity);
//     //         }

//     //         $cartItem->delete();
//     //     });

//     //     return response()->json(['message' => 'Removed from cart']);
//     // }
//     public function remove($id)
//     {
//         $cartItem = Cart::where('id', $id)
//             ->where('user_id', Auth::id())
//             ->firstOrFail();

//         $cartItem->delete();

//         return response()->json(['message' => 'Removed from cart']);
//     }

//     // public function updateQuantity(Request $request, $id)
//     // {
//     //     $request->validate(['quantity' => 'required|integer|min:0']);

//     //     \DB::transaction(function () use ($request, $id) {
//     //         $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

//     //         $oldQty = $cartItem->quantity;
//     //         $newQty = $request->quantity;

//     //         $diff = $oldQty - $newQty; // positive = stock to return

//     //         if ($diff > 0 && $cartItem->variant_id) {
//     //             \App\Models\ProductVariant::where('id', $cartItem->variant_id)
//     //                 ->increment('stock_quantity', $diff);
//     //         } elseif ($diff < 0 && $cartItem->variant_id) {
//     //             // User increased quantity — check stock
//     //             $variant = \App\Models\ProductVariant::where('id', $cartItem->variant_id)->first();
//     //             if ($variant->stock_quantity < abs($diff)) {
//     //                 throw new \Exception('Not enough stock');
//     //             }
//     //             $variant->decrement('stock_quantity', abs($diff));
//     //         }

//     //         if ($newQty <= 0) {
//     //             $cartItem->delete();
//     //         } else {
//     //             $cartItem->quantity = $newQty;
//     //             $cartItem->save();
//     //         }
//     //     });

//     //     return response()->json(['message' => 'Quantity updated']);
//     // }
//     public function updateQuantity(Request $request, $id)
//     {
//         $request->validate(['quantity' => 'required|integer|min:0']);

//         $cartItem = Cart::where('id', $id)
//             ->where('user_id', Auth::id())
//             ->firstOrFail();

//         if ($request->quantity <= 0) {
//             $cartItem->delete();
//         } else {
//             $cartItem->quantity = $request->quantity;
//             $cartItem->save();
//         }

//         return response()->json(['message' => 'Quantity updated']);
//     }
// }


namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    // Get cart count (for badge)
    public function count()
    {
        $count = Cart::where('user_id', Auth::id())->sum('quantity');
        return response()->json(['count' => (int) $count]);
    }

    // ADD TO CART – Matches Menu.jsx custom item structure
    public function add(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'item_name'    => 'required|string|max:255',
            'selections'   => 'required|array',
            'selections.*' => 'array',
            'selections.*.name' => 'required|string',
            'selections.*.price' => 'nullable|numeric',
            'total_price'  => 'required|numeric|min:0',
            'section'      => 'nullable|string',
            'quantity'     => 'sometimes|integer|min:1',
        ]);

        $quantity = $request->quantity ?? 1;

        // Optional: Prevent duplicate identical items (same name + same selections)
        // You can skip this if you want to allow duplicates
        $existing = Cart::where('user_id', $userId)
            ->where('item_name', $request->item_name)
            ->where('selections', json_encode($request->selections))
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);
            $cartItem = $existing;
        } else {
            $cartItem = Cart::create([
                'user_id'     => $userId,
                'item_name'   => $request->item_name,
                'selections'  => $request->selections, // stored as JSON
                'total_price' => $request->total_price,
                'quantity'    => $quantity,
                'section'     => $request->section ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Added to cart',
            'cartItem' => [
                'id'         => $cartItem->id,
                'base'       => $cartItem->item_name,
                'selections' => $cartItem->selections,
                'totalPrice' => (float) $cartItem->total_price,
                'quantity'   => $cartItem->quantity,
            ]
        ]);
    }

    // GET CART – Already perfect, keep as-is
    public function index()
    {
        try {
            $items = Cart::where('user_id', Auth::id())
                ->orderBy('created_at', 'asc')
                ->get();

            $cartData = $items->map(function ($cartItem) {
                $selections = is_array($cartItem->selections)
                    ? $cartItem->selections
                    : json_decode($cartItem->selections, true) ?? [];

                // Normalize selections to always have {name}
                $selections = collect($selections)->map(function ($sel) {
                    if (is_string($sel)) return ['name' => $sel];
                    if (is_array($sel) && isset($sel['name'])) return ['name' => $sel['name']];
                    return ['name' => 'Unknown option'];
                })->toArray();

                return [
                    'id'         => $cartItem->id,
                    'base'       => $cartItem->item_name,
                    'selections' => $selections,
                    'totalPrice' => (float) $cartItem->total_price,
                    'quantity'   => $cartItem->quantity,
                ];
            });

            $totalAmount = $items->sum(fn($item) => $item->total_price * $item->quantity);

            return response()->json([
                'cart'  => $cartData,
                'total' => number_format($totalAmount, 2),
            ]);
        } catch (\Exception $e) {
            \Log::error('Cart index error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load cart'], 500);
        }
    }

    // UPDATE QUANTITY
    public function updateQuantity(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        $cartItem = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($request->quantity <= 0) {
            $cartItem->delete();
            return response()->json(['message' => 'Item removed']);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Quantity updated']);
    }

    // REMOVE ITEM
    public function remove($id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['message' => 'Removed from cart']);
    }

    // OPTIONAL: Clear entire cart
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}