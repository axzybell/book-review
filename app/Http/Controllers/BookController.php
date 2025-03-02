<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Gets the title from the URL (e.g., /books?title=example)
        $title = $request->input('title');

        //Gets the filter (e.g., /books?filter=popular_last_month).
        $filter = $request->input('filter', '');

        //if title not null or not empty will run the func(), if title null will select * from books
        $books = Book::when(
            $title,
            fn($query, $title) => $query->title($title)
        );

        $books = match($filter) {
            'popular_last_month' => $books->popularLastMonth(),
            'popular_last_6_months' => $books->popularLast6Months(),
            'highest_rated_last_month' => $books->highestRatedLastMonth(),
            'highest_rated_last_6_months' => $books->highestRatedLast6Months(),
            default => $books->latest()->withAvgRating()->withReviewsCount()
        };

        // use cache bcuz improves performance by avoiding repeated database queries, Reduces load on the database and Faster page loads.
        //$cacheKey = .../books?title=rep&filter=highest_rated_last_6_months
        $cacheKey = 'books:' . $filter . ':' . $title;

        //If data exists in cache → It returns cached data.
        //If data does NOT exist → It executes $callback (fn() => $books->get()), stores it in the cache, and returns the data.
        $books =
        cache()->remember(
            $cacheKey,
            3600,
            fn() =>
            $books->paginate(10)
        );

        return view('books.index', ['books' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $cacheKey = 'book:' . $id;

        $book = cache()->remember(
            $cacheKey,
            3600,
            fn() =>
            Book::with([
                'reviews' => fn($query) => $query->latest()
            ])->withAvgRating()->withReviewsCount()->findOrFail($id)
        );

        return view('books.show', ['book' => $book]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
