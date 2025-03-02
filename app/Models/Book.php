<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;

class Book extends Model
{
    use HasFactory;

    public function reviews() {
        //means that a book can many review
        return $this->hasMany(Review::class);
    }

    //all query scope get atleast 1 argument '$query'

    //cmd \App\Models\Book::title('quos')->get();
    public function scopeTitle(Builder $query, string $title): Builder {
        return $query->where('title', 'LIKE', '%'. $title .'%');
    }

    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null):Builder|QueryBuilder {
        return $query->withCount([
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ]);
    }

    public function scopeWithAvgRating(Builder $query, $from = null, $to = null):Builder|QueryBuilder {
        return $query->withAvg([
            //arrow function, fn instead of function same thing
            'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ], 'rating');
    }


    //cmd \App\Models\Book::popular()->highestRated()->get(); to get reviews_count first only see the highest rating with the avg_rating
    // Fetch books ordered by the number of reviews, highest to lowest
    public function scopePopular(Builder $query):Builder {
        return $query->withReviewsCount()
            ->orderBy('reviews_count', 'desc'); //sorted by reviews_count (highest first).
    }

    public function scopeHighestRated(Builder $query): Builder|QueryBuilder {
        return $query->withAvgRating()
            ->orderBy('reviews_avg_rating', 'desc'); //sorted by reviews_avg_rating (highest first).
    }

    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder {
        return $query->having('reviews_count', '>=', $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null) {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } else if (!$from && $to) {
            $query->where('created_at', '<=', $to);
        } else if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    public function scopePopularLastMonth(Builder $query): Builder|QueryBuilder {
        return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder|QueryBuilder {
        return $query->popular(now()->subMonth(6), now())
            ->highestRated(now()->subMonth(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder|QueryBuilder {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query): Builder|QueryBuilder {
        return $query->highestRated(now()->subMonth(6), now())
            ->popular(now()->subMonth(6), now())
            ->minReviews(5);
    }

    protected static function booted() {
        static::updated(fn(Book $book) => cache()->forget('book:' . $book->id));

        static::deleted(fn(Book $book) => cache()->forget('book:' . $book->id));
    }
}
