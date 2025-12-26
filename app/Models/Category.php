<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    
    /**
     * Get all books in this category
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
