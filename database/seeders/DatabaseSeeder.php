<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Accounts -------------------------------------------------------
        User::updateOrCreate(
            ['email' => 'admin@ebook.test'],
            [
                'name' => 'Store Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@ebook.test'],
            [
                'name' => 'Jane Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        // --- Categories -----------------------------------------------------
        $categoryNames = ['Fiction', 'Science', 'Technology', 'Business', 'Children'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }

        // --- Books ----------------------------------------------------------
        $books = [
            ['Fiction',    'The Midnight Library',   'Matt Haig',         1299, 25],
            ['Fiction',    'Where the Crawdads Sing', 'Delia Owens',      1499, 18],
            ['Science',    'A Brief History of Time', 'Stephen Hawking',   1099, 30],
            ['Science',    'Cosmos',                  'Carl Sagan',        1399,  0], // out of stock sample
            ['Technology', 'Clean Code',              'Robert C. Martin',  3299, 40],
            ['Technology', 'The Pragmatic Programmer', 'Hunt & Thomas',    3599, 22],
            ['Business',   'Atomic Habits',           'James Clear',       1799, 50],
            ['Business',   'Zero to One',             'Peter Thiel',       1599, 15],
            ['Children',   'The Gruffalo',            'Julia Donaldson',    899, 60],
            ['Children',   'Matilda',                 'Roald Dahl',         999, 35],
        ];

        foreach ($books as [$category, $title, $author, $priceCents, $stock]) {
            Book::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'category_id' => $categories[$category]->id,
                    'title' => $title,
                    'author' => $author,
                    'description' => "A sample seeded description for \"{$title}\" by {$author}.",
                    'price_cents' => $priceCents,
                    'stock_qty' => $stock,
                    'is_active' => true,
                ]
            );
        }

        // Mark some titles down (compare_at = original list price) so the
        // storefront shows "% OFF" deals out of the box.
        $deals = [
            'the-midnight-library' => 1999,
            'clean-code' => 4499,
            'atomic-habits' => 2499,
            'the-pragmatic-programmer' => 4999,
            'zero-to-one' => 2499,
            'matilda' => 1499,
            'cosmos' => 2499,
        ];
        foreach ($deals as $slug => $compareAtCents) {
            Book::where('slug', $slug)->update(['compare_at_cents' => $compareAtCents]);
        }

        // Generate placeholder cover images for a demo-ready catalogue.
        $this->call('books:covers');
    }
}
