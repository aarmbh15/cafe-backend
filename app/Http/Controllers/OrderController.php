<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class OrderController extends Controller
// {
//     public function index(Request $request)
//     {
//         $user = $request->user();

//         $orders = $user->orders()
//             ->with(['items', 'address'])
//             ->orderByDesc('created_at')
//             ->get();

//         return response()->json([
//             'data' => $orders
//         ]);
//     }
// }


namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use App\Http\Controllers\Mail;
use App\Mail\NewOrderNotification;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with('items')
            ->orderByDesc('placed_at')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    /**
     * Store a new order from the cart (checkout).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate incoming data from frontend
        $validated = $request->validate([
            'customer.name'       => 'required|string|max:255',
            'customer.email'      => 'required|email',
            'customer.phone'      => 'required|string|max:30',
            'customer.notes'      => 'nullable|string|max:1000',
            'payment_method'      => 'required|in:card,cash',
            'items'               => 'required|array|min:1',
            'items.*.id'          => 'required|integer|exists:cart,id,user_id,' . $user->id,
            'items.*.base'        => 'required|string',
            'items.*.selections'  => 'required|array',
            'items.*.totalPrice'  => 'required|numeric|min:0',
            'items.*.quantity'    => 'required|integer|min:1',
            'total_amount'        => 'required|numeric|min:0',
            'address_id' => 'nullable|integer|exists:user_addresses,id,user_id,' . $user->id,
        ]);

        // Optional: Double-check total matches cart (security)
        $cartItems = Cart::where('user_id', $user->id)->get();
        $calculatedTotal = $cartItems->sum(fn($item) => $item->total_price * $item->quantity);

        if (abs($calculatedTotal - $validated['total_amount']) > 0.01) {
            return response()->json([
                'message' => 'Total amount mismatch. Please refresh your cart.'
            ], 422);
        }

        return DB::transaction(function () use ($validated, $user, $cartItems) {
            // Create the order
            $order = Order::create([
                'user_id'        => $user->id,
                'order_number'   => Order::generateOrderNumber(),
                'items_total'    => $validated['total_amount'],
                'delivery_fee'   => 0, // For collection only – adjust if you add delivery later
                'total'          => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'customer_notes' => $validated['customer']['notes'] ?? null,
                'address_id'     => $validated['address_id'] ?? null,
                'placed_at'      => now(),
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'item_name'   => $cartItem->item_name,
                    'selections'  => $cartItem->selections,
                    'total_price' => $cartItem->total_price,
                    'quantity'    => $cartItem->quantity,
                    'line_total'  => $cartItem->total_price * $cartItem->quantity,
                    'section'     => $cartItem->section,
                ]);
            }

            // Clear the user's cart
            Cart::where('user_id', $user->id)->delete();

            // Load items for response
            $order->load('items');

            // Send notification email
            try {
                Mail::to(['itsroy2885@gmail.com'])
                    ->send(new NewOrderNotification($order));
            } catch (\Exception $e) {
                \Log::error('Failed to send new order email: ' . $e->getMessage());
                // Don't fail the order if email fails
            }

            return response()->json([
                'message' => 'Order placed successfully!',
                'order'   => $order,
            ], 201);
        });
    }
}