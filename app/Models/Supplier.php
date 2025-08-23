<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(TailorTransaction::class, 'supplier_id');
    }
}
