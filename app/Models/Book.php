<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'external_id',
        'title',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'category_id',
        'description',
        'cover_image',
        'total_copies',
        'available_copies',
    ];
    
    protected $casts = [
        'publication_year' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
    ];
    
    /**
     * Get the category that owns the book
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get all transactions for this book
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
    /**
     * Get book recommendations
     */
    public function bookRecommendations()
    {
        return $this->hasMany(BookRecommendation::class);
    }
    
    /**
     * Scope a query to search books
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
    
    /**
     * Scope a query to filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
    
    /**
     * Scope a query to get available books
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0);
    }
    
    /**
     * Check if book is available for borrowing
     */
    public function isAvailable()
    {
        return $this->available_copies > 0;
    }
    
    /**
     * Decrease available copies when borrowed
     */
    public function decreaseAvailability()
    {
        if ($this->available_copies > 0) {
            $this->decrement('available_copies');
            return true;
        }
        return false;
    }
    
    /**
     * Increase available copies when returned
     */
    public function increaseAvailability()
    {
        if ($this->available_copies < $this->total_copies) {
            $this->increment('available_copies');
            return true;
        }
        return false;
    }
}
