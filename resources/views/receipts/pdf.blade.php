<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .wrap { padding: 32px 40px; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 14px; }
        .header h1 { margin: 0; font-size: 20px; color: #111827; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; padding-top: 16px; font-size: 12px; }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 9999px;
            font-size: 10px; font-weight: bold; background: #ecfdf5; color: #047857;
        }
        .items { margin-top: 24px; }
        .items th {
            text-align: left; font-size: 10px; text-transform: uppercase;
            color: #6b7280; border-bottom: 1px solid #e5e7eb; padding: 8px 4px;
        }
        .items td { padding: 8px 4px; border-bottom: 1px solid #f3f4f6; }
        .tc { text-align: center; }
        .tr { text-align: right; }
        .totals { margin-top: 18px; width: 40%; float: right; }
        .totals td { padding: 4px 0; }
        .totals .grand td {
            border-top: 1px solid #e5e7eb; font-weight: bold; font-size: 14px; padding-top: 8px;
        }
        .footer { clear: both; margin-top: 60px; text-align: center; color: #9ca3af; font-size: 10px; }
        .paid-stamp {
            position: absolute; top: 120px; right: 60px;
            border: 3px solid #16a34a; color: #16a34a; opacity: .85;
            font-size: 28px; font-weight: bold; letter-spacing: 2px;
            padding: 4px 16px; border-radius: 8px; transform: rotate(-12deg);
        }
    </style>
</head>
<body>
    <div class="wrap">
        @if ($order->status === 'paid')
            <div class="paid-stamp">PAID</div>
        @endif

        <table class="header">
            <tr>
                <td>
                    <h1>{{ config('store.name') }}</h1>
                    <div class="muted">Official Receipt</div>
                </td>
                <td class="right">
                    <div style="font-size:14px; font-weight:bold;">{{ $order->order_number }}</div>
                    <div class="muted">{{ $order->created_at->format('M j, Y g:i A') }}</div>
                    <div style="margin-top:4px;"><span class="badge">{{ strtoupper($order->status) }}</span></div>
                </td>
            </tr>
        </table>

        <table class="meta">
            <tr>
                <td width="55%">
                    <strong>Billed to</strong><br>
                    {{ $order->shipping_name }}<br>
                    {!! nl2br(e($order->shipping_address)) !!}<br>
                    {{ $order->shipping_phone }}<br>
                    <span class="muted">{{ $order->user->email ?? '' }}</span>
                </td>
                <td width="45%" class="right">
                    <strong>Payment</strong><br>
                    {{ ucfirst(str_replace('_', ' ', $order->payment->gateway ?? '')) }}<br>
                    <span class="muted">{{ $order->payment->transaction_ref ?? '' }}</span><br>
                    {{ ucfirst($order->payment->status ?? '') }}
                    @if ($order->payment && $order->payment->paid_at)
                        <br><span class="muted">{{ $order->payment->paid_at->format('M j, Y g:i A') }}</span>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="tc">Qty</th>
                    <th class="tr">Unit</th>
                    <th class="tr">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->title }}</td>
                        <td class="tc">{{ $item->quantity }}</td>
                        <td class="tr">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                        <td class="tr">${{ number_format($item->lineTotalCents() / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="muted">Subtotal</td>
                <td class="tr">${{ number_format($order->subtotal_cents / 100, 2) }}</td>
            </tr>
            <tr>
                <td class="muted">Tax</td>
                <td class="tr">${{ number_format($order->tax_cents / 100, 2) }}</td>
            </tr>
            <tr class="grand">
                <td>Total</td>
                <td class="tr">${{ number_format($order->total_cents / 100, 2) }}</td>
            </tr>
        </table>

        <div class="footer">
            Thank you for shopping with {{ config('store.name') }}.<br>
            Questions? {{ config('store.support_email') }}
        </div>
    </div>
</body>
</html>
