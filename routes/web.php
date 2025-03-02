<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('books.index');
});

Route::resource('books', BookController::class)
    ->only(['index', 'show']);

    Route::resource('books.reviews', ReviewController::class)
    // The {review} parameter belongs to a specific {book} and It will look up reviews only within that book
    ->scoped(['review' => 'book'])
    //Removes the POST /books/{book}/reviews route, so store() must be manually defined.
    ->except(['store']);

Route::post('books/{book}/reviews', [ReviewController::class, 'store'])
    ->name('books.reviews.store')
    ->middleware('throttle:reviews'); // Manually add store route with middleware
