<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateBookCovers extends Command
{
    protected $signature = 'books:covers {--force : Regenerate even for books that already have a cover}';

    protected $description = 'Generate placeholder SVG cover images for books (demo data).';

    public function handle(): int
    {
        $books = Book::withTrashed()->get();
        $made = 0;

        foreach ($books as $book) {
            if ($book->cover_image && ! $this->option('force')) {
                continue;
            }

            $path = "covers/{$book->slug}.svg";
            Storage::disk('public')->put($path, $this->svg($book));
            $book->forceFill(['cover_image' => $path])->save();
            $made++;
        }

        $this->info("Generated {$made} cover(s).");

        return self::SUCCESS;
    }

    private function svg(Book $book): string
    {
        $palettes = [
            ['#4f46e5', '#7c3aed'], ['#0ea5e9', '#2563eb'], ['#059669', '#10b981'],
            ['#db2777', '#e11d48'], ['#d97706', '#f59e0b'], ['#475569', '#0f172a'],
        ];
        [$c1, $c2] = $palettes[crc32($book->title) % count($palettes)];

        $titleLines = $this->wrap($book->title, 14, 4);
        $author = htmlspecialchars($book->author, ENT_QUOTES);

        $titleSvg = '';
        $y = 170;
        foreach ($titleLines as $line) {
            $titleSvg .= '<text x="30" y="'.$y.'" font-family="Georgia, serif" font-size="28" font-weight="700" fill="#ffffff">'.htmlspecialchars($line, ENT_QUOTES).'</text>';
            $y += 36;
        }

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 400" width="300" height="400">
            <defs>
                <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="{$c1}"/>
                    <stop offset="100%" stop-color="{$c2}"/>
                </linearGradient>
            </defs>
            <rect width="300" height="400" fill="url(#g)"/>
            <rect x="14" y="14" width="272" height="372" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
            {$titleSvg}
            <text x="30" y="360" font-family="Arial, sans-serif" font-size="15" fill="rgba(255,255,255,0.85)">{$author}</text>
        </svg>
        SVG;
    }

    /** Wrap text into at most $maxLines lines of roughly $width chars. */
    private function wrap(string $text, int $width, int $maxLines): array
    {
        $lines = explode("\n", wordwrap($text, $width, "\n", true));

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1]).'…';
        }

        return $lines;
    }
}
