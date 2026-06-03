<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'book_id', 'title', 'unit_price_cents', 'quantity'];

    protected $casts = [
        'unit_price_cents' => 'integer',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** May be null if the underlying book was later deleted. */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** Line total in integer cents (snapshot price × quantity). */
    public function lineTotalCents(): int
    {
        return $this->unit_price_cents * $this->quantity;
    }
}
