<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorTransactionProduct extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function tailorTransaction()
    {
        return $this->belongsTo(TailorTransaction::class, 'tailor_transaction_id', 'id');
    }
}
