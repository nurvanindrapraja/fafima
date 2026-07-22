<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = ['family_id','created_by','name','target_amount','current_amount','target_date','status'];
    protected $casts = ['target_date' => 'date', 'target_amount' => 'decimal:2', 'current_amount' => 'decimal:2'];

    public function family() { return $this->belongsTo(Family::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approvals() { return $this->hasMany(TargetApproval::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
}
