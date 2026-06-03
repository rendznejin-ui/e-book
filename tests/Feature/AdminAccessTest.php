<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_cannot_access_the_admin_area(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']));

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/books')->assertForbidden();
    }

    public function test_a_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_an_admin_can_view_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/admin')->assertOk();
    }

    public function test_an_admin_can_create_a_book(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $this->actingAs($admin)->post('/admin/books', [
            'title' => 'A Brand New Book',
            'author' => 'An Author',
            'category_id' => $category->id,
            'price' => 19.99,
            'stock_qty' => 12,
            'is_active' => '1',
        ])->assertRedirect('/admin/books');

        $book = Book::where('title', 'A Brand New Book')->first();
        $this->assertNotNull($book);
        $this->assertSame(1999, $book->price_cents);   // dollars -> cents
        $this->assertSame('a-brand-new-book', $book->slug);
    }

    public function test_a_customer_cannot_create_a_book(): void
    {
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->post('/admin/books', [
                'title' => 'Hacker Book',
                'author' => 'X',
                'category_id' => $category->id,
                'price' => 1,
                'stock_qty' => 1,
            ])->assertForbidden();

        $this->assertDatabaseMissing('books', ['title' => 'Hacker Book']);
    }
}
