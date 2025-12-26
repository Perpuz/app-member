<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
    ];
    
    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];
    
    const STATUS_BORROWED = 'borrowed';
    const STATUS_RETURNED = 'returned';
    const STATUS_OVERDUE = 'overdue';
    
    const FINE_PER_DAY = 2000; // Rp 2000 per hari
    
    /**
     * Get the user that owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the book that is borrowed
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    /**
     * Scope a query to get active transactions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_BORROWED, self::STATUS_OVERDUE]);
    }
    
    /**
     * Scope a query to get overdue transactions
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                     ->orWhere(function($q) {
                         $q->where('status', self::STATUS_BORROWED)
                           ->where('due_date', '<', now());
                     });
    }
    
    /**
     * Scope a query to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Check if transaction is overdue
     */
    public function  isOverdue()
    {
        if ($this->status === self::STATUS_RETURNED) {
            return false;
        }
        
        return Carbon::parse($this->due_date)->isPast();
    }
    
    /**
     * Calculate fine amount based on overdue days
     */
    public function calculateFine()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $returnDate = $this->return_date ?? now();
        $daysOverdue = Carbon::parse($this->due_date)->diffInDays($returnDate);
        
        return $daysOverdue * self::FINE_PER_DAY;
    }
    
    /**
     * Update transaction status to overdue if past due date
     */
    public function checkAndUpdateOverdueStatus()
    {
        if ($this->isOverdue() && $this->status === self::STATUS_BORROWED) {
            $this->status = self::STATUS_OVERDUE;
            $this->fine_amount = $this->calculateFine();
            $this->save();
        }
    }
    
    /**
     * Process book return
     */
    public function processReturn()
    {
        $this->return_date = now();
        $this->status = self::STATUS_RETURNED;
        
        // Calculate fine if overdue
        if ($this->isOverdue()) {
            $this->fine_amount = $this->calculateFine();
        }
        
        $this->save();
        
        // Increase book availability
        $this->book->increaseAvailability();
        
        return $this;
    }
}
