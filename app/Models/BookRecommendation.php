<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'score',
    ];
    
    protected $casts = [
        'score' => 'decimal:2',
    ];
    
    /**
     * Get the user for this recommendation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the recommended book
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    /**
     * Scope a query to get high scoring recommendations
     */
    public function scopeTopRated($query, $minScore = 4.0)
    {
        return $query->where('score', '>=', $minScore)
                     ->orderBy('score', 'desc');
    }
}
