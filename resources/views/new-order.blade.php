<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order #{{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h1 { color: #064e3b; text-align: center; }
        .order-info { background: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .label { font-weight: bold; color: #064e3b; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #064e3b; color: white; }
        .total-row { font-weight: bold; font-size: 1.1em; background: #f0fdf4; }
        .footer { text-align: center; margin-top: 40px; color: #666; font-size: 14px; }
        .notes { background: #fff9c4; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #fbc02d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Order Received!</h1>
        <p style="text-align: center; color: #666;">Order placed on {{ $order->placed_at->format('d M Y \a\t h:i A') }}</p>

        <div class="order-info">
            <p><span class="label">Order Number:</span> #{{ $order->order_number }}</p>
            <p><span class="label">Customer Name:</span> {{ $order->customer_name ?? $order->user->first_name  }}</p>
            <p><span class="label">Email:</span> {{ $order->customer_email ?? $order->user->email }}</p>
            <p><span class="label">Phone:</span> {{ $order->customer_phone ?? 'Not provided' ?? $order->user->phone }}</p>
            <p><span class="label">Payment Method:</span> {{ ucfirst($order->payment_method) }}</p>
        </div>

        @if($order->customer_notes)
            <div class="notes">
                <span class="label">Customer Notes:</span><br>
                {{ $order->customer_notes }}
            </div>
        @endif

        <h2 style="color: #064e3b;">Order Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Selections</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td><strong>{{ $item->item_name }}</strong></td>
                        <td>
                            @if(is_array($item->selections))
                                @foreach($item->selections as $key => $value)
                                    <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                                @endforeach
                            @else
                                {{ $item->selections ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>£{{ number_format($item->total_price, 2) }}</td>
                        <td>£{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;"><strong>Grand Total:</strong></td>
                    <td><strong>£{{ number_format($order->total, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Thank you for the order!<br>
            <strong>Café Lamees System</strong>
        </div>
    </div>
</body>
</html>