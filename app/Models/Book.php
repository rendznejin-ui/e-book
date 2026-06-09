<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'title', 'slug', 'author', 'isbn', 'description',
        'price_cents', 'compare_at_cents', 'stock_qty', 'cover_image', 'preview_pdf', 'is_active',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'compare_at_cents' => 'integer',
        'stock_qty' => 'integer',
        'is_active' => 'boolean',
    ];

    /** True when a higher list price is set — i.e. the book is marked down. */
    public function onSale(): bool
    {
        return $this->compare_at_cents !== null && $this->compare_at_cents > $this->price_cents;
    }

    /** Whole-number discount percentage (e.g. 40 for "40% OFF"). */
    public function discountPercent(): int
    {
        if (! $this->onSale()) {
            return 0;
        }

        return (int) round((1 - $this->price_cents / $this->compare_at_cents) * 100);
    }

    /** Formatted list price, e.g. "24.99". */
    protected function comparePrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->compare_at_cents ? number_format($this->compare_at_cents / 100, 2) : null,
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Human-readable price derived from integer cents, e.g. 1999 -> "19.99". */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->price_cents / 100, 2),
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
