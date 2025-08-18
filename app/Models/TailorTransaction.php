<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorTransaction extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(TailorTransactionItem::class);
    }

    public function tailor()
    {
        return $this->belongsTo(User::class, 'tailor_id');
    }

    public function commission()
    {
        return $this->hasOne(TailorCommission::class, 'tailor_transaction_id');
    }
}
