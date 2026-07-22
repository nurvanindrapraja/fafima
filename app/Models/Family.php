<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['name', 'code', 'qr_code', 'allow_member_view_all_transactions'];

    public function members()
    {
        return $this->hasMany(User::class);
    }
}
