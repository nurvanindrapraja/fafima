<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['family_id', 'name', 'type', 'is_default', 'created_by'];

    public function transactions() { return $this->hasMany(Transaction::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
