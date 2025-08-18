<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorCommission extends Model
{
    protected $guarded = [];

    public function transaction()
    {
        return $this->belongsTo(TailorTransaction::class, 'tailor_transaction_id');
    }
}
