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
        'price_cents', 'stock_qty', 'cover_image', 'preview_pdf', 'is_active',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'stock_qty' => 'integer',
        'is_active' => 'boolean',
    ];

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
