<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitTenant extends Model
{
    protected $fillable = ['unit_id', 'user_id', 'from_date', 'to_date'];

    public function unit() { 
        return $this->belongsTo(Unit::class); 
    }
    
    public function user() { 
        return $this->belongsTo(User::class); 
    }
}
