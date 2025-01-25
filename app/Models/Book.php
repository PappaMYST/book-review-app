<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    //Local Query Scope
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', "%$title%");
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder
    {
        return $query
            ->withCount([
                'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
            ])
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder
    {
        return $query
            ->withAvg([
                'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
            ], 'rating')
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReview(Builder $query, int $minReview): Builder
    {
        return $query->having('reviews_count', '>=', $minReview);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null)
    {
        if ($from && !$to) {
            $query->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $query->where('created_at', '<=', '$to');
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    public function scopePopularLastMonth(Builder $query): Builder
    {
        return $query
            ->popular(now()->subMonths(), now())
            ->highestrated(now()->subMonths(), now())
            ->minReview(2);
    }

    public function scopePopularLastYear(Builder $query): Builder
    {
        return $query
            ->popular(now()->subMonths(12), now())
            ->highestrated(now()->subMonths(12), now())
            ->minReview(6);
    }

    public function scopePopularAllTime(Builder $query): Builder
    {
        return $query
            ->popular()
            ->highestrated()
            ->minReview(15);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder
    {
        return $query
            ->highestrated(now()->subMonths(), now())
            ->popular(now()->subMonths(), now())
            ->minReview(2);
    }

    public function scopeHighestRatedLastYear(Builder $query): Builder
    {
        return $query
            ->highestrated(now()->subMonths(12), now())
            ->popular(now()->subMonths(12), now())
            ->minReview(6);
    }

    public function scopeHighestRatedAllTime(Builder $query): Builder
    {
        return $query
            ->highestrated()
            ->popular()
            ->minReview(15);
    }
}
