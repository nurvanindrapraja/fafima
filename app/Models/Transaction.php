<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'family_id', 'user_id', 'category_id', 'target_id', 'type',
        'amount', 'date', 'description', 'source',
        'receipt_path', 'is_target_funding'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_target_funding' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function family() { return $this->belongsTo(Family::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function target() { return $this->belongsTo(Target::class); }
    public function logs() { return $this->hasMany(TransactionLog::class); }
}
