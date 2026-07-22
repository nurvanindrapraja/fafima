<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetApproval extends Model
{
    protected $fillable = ['target_id','user_id','status'];

    public function target() { return $this->belongsTo(Target::class); }
    public function user() { return $this->belongsTo(User::class); }
}
