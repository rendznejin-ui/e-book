<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\Payment\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** Place an order for the given user/book and return the pending Order. */
    private function placeOrder(User $user, Book $book, int $qty = 2): Order
    {
        $this->actingAs($user);

        // Seed the cart via the real service, then post the checkout form.
        app(CartService::class)->add($book, $qty);

        $this->post('/checkout', [
            'shipping_name' => 'Jane Buyer',
            'shipping_address' => '1 Book St',
            'shipping_phone' => '555-0100',
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    private function signatureFor(Order $order): string
    {
        return app(PaymentGateway::class)->createQrPayload($order->payment->fresh())['signature'];
    }

    public function test_placing_an_order_snapshots_items_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['price_cents' => 1000, 'stock_qty' => 5]);

        $order = $this->placeOrder($user, $book, 2);

        $this->assertSame('pending', $order->status);
        $this->assertSame(2000, $order->total_cents);

        $item = $order->items()->first();
        $this->assertSame($book->title, $item->title);          // snapshot
        $this->assertSame(1000, $item->unit_price_cents);       // snapshot
        $this->assertSame(2, $item->quantity);

        $this->assertSame(3, $book->fresh()->stock_qty);        // 5 - 2
        $this->assertSame('initiated', $order->payment->status);
        $this->assertSame(2000, $order->payment->amount_cents);
    }

    public function test_valid_confirmation_marks_paid_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['price_cents' => 1500, 'stock_qty' => 10]);
        $order = $this->placeOrder($user, $book, 1);

        $this->post("/checkout/{$order->id}/confirm", ['signature' => $this->signatureFor($order)])
            ->assertOk()
            ->assertJson(['status' => 'paid']);

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame('succeeded', $order->payment->status);
        $this->assertNotNull($order->payment->paid_at);
        $this->assertSame(0, (int) app(CartService::class)->count());
    }

    public function test_forged_signature_is_rejected(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['stock_qty' => 5]);
        $order = $this->placeOrder($user, $book, 1);

        $this->post("/checkout/{$order->id}/confirm", ['signature' => 'forged'])
            ->assertStatus(422);

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_a_non_owner_cannot_pay_someone_elses_order(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['stock_qty' => 5]);
        $order = $this->placeOrder($owner, $book, 1);
        $signature = $this->signatureFor($order);

        $this->actingAs(User::factory()->create()); // a different user
        $this->post("/checkout/{$order->id}/confirm", ['signature' => $signature])
            ->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_confirmation_is_idempotent(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['price_cents' => 1000, 'stock_qty' => 5]);
        $order = $this->placeOrder($user, $book, 2);
        $signature = $this->signatureFor($order);

        $this->post("/checkout/{$order->id}/confirm", ['signature' => $signature])->assertOk();
        $this->post("/checkout/{$order->id}/confirm", ['signature' => $signature])->assertOk();

        // Stock decremented exactly once (5 - 2), order paid, single payment.
        $this->assertSame(3, $book->fresh()->stock_qty);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, $order->payment()->count());
    }

    public function test_cancelling_a_pending_order_restores_stock(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['stock_qty' => 5]);
        $order = $this->placeOrder($user, $book, 2);
        $this->assertSame(3, $book->fresh()->stock_qty);

        $this->post("/checkout/{$order->id}/cancel")->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $book->fresh()->stock_qty); // restored
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
    }
}
