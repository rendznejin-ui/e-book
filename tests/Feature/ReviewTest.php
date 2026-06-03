<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** Give the user a paid order containing the book (a verified purchase). */
    private function purchase(User $user, Book $book): void
    {
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-'.$user->id,
            'status' => 'paid',
            'subtotal_cents' => $book->price_cents,
            'tax_cents' => 0,
            'total_cents' => $book->price_cents,
            'shipping_name' => 'T',
            'shipping_address' => 'T',
            'shipping_phone' => 'T',
        ]);

        $order->items()->create([
            'book_id' => $book->id,
            'title' => $book->title,
            'unit_price_cents' => $book->price_cents,
            'quantity' => 1,
        ]);
    }

    public function test_a_verified_purchaser_can_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);

        $this->actingAs($user)->post("/books/{$book->slug}/reviews", [
            'rating' => 5,
            'comment' => 'Excellent read.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);
    }

    public function test_a_non_purchaser_cannot_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post("/books/{$book->slug}/reviews", [
            'rating' => 4,
        ])->assertForbidden();

        $this->assertDatabaseMissing('reviews', ['book_id' => $book->id]);
    }

    public function test_resubmitting_updates_the_existing_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $this->purchase($user, $book);

        $this->actingAs($user)->post("/books/{$book->slug}/reviews", ['rating' => 3]);
        $this->actingAs($user)->post("/books/{$book->slug}/reviews", ['rating' => 5, 'comment' => 'Changed my mind.']);

        $this->assertSame(1, $book->reviews()->count());
        $this->assertSame(5, $book->reviews()->first()->rating);
    }
}
