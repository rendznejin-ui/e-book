<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /** A representative emoji icon for the category, chosen from its name. */
    public function icon(): string
    {
        $name = strtolower($this->name);

        return match (true) {
            str_contains($name, 'non') && str_contains($name, 'fiction') => '📰',
            str_contains($name, 'fiction') => '📖',
            str_contains($name, 'science') => '🔬',
            str_contains($name, 'tech') => '💻',
            str_contains($name, 'business'), str_contains($name, 'money') => '💼',
            str_contains($name, 'child'), str_contains($name, 'kid') => '🧸',
            str_contains($name, 'fantasy') => '🐉',
            str_contains($name, 'romance') => '💖',
            str_contains($name, 'history') => '🏛️',
            str_contains($name, 'cook'), str_contains($name, 'food') => '🍳',
            str_contains($name, 'art') => '🎨',
            str_contains($name, 'health') => '🌿',
            str_contains($name, 'travel') => '🧭',
            str_contains($name, 'comic'), str_contains($name, 'manga') => '💥',
            str_contains($name, 'edu') => '🎓',
            default => '📚',
        };
    }
}
