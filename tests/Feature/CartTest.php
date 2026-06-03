<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_add_a_book_to_the_cart(): void
    {
        $book = Book::factory()->create(['price_cents' => 1299, 'stock_qty' => 10]);

        $this->postJson('/cart/add', ['book_id' => $book->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['count' => 2, 'subtotal' => '25.98']);
    }

    public function test_quantity_cannot_exceed_available_stock(): void
    {
        $book = Book::factory()->create(['price_cents' => 1000, 'stock_qty' => 3]);

        // Ask for 10, only 3 in stock -> clamped to 3.
        $this->postJson('/cart/add', ['book_id' => $book->id, 'quantity' => 10])
            ->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_an_out_of_stock_book_cannot_be_added(): void
    {
        $book = Book::factory()->outOfStock()->create();

        $this->postJson('/cart/add', ['book_id' => $book->id, 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_a_guest_cart_merges_into_the_user_on_login(): void
    {
        $book = Book::factory()->create(['stock_qty' => 10]);
        $user = User::factory()->create();

        // Guest adds to cart (session-tracked).
        $this->postJson('/cart/add', ['book_id' => $book->id, 'quantity' => 2])->assertOk();

        // Authenticate, then resolve the cart through a real request so the
        // session-tracked guest cart merges into the user's cart.
        $this->actingAs($user);
        $this->get('/cart')->assertOk();

        $cart = \App\Models\Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($cart);
        $this->assertSame(2, (int) $cart->items()->sum('quantity'));
    }
}
