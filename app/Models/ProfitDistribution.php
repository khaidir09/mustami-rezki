<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitDistribution extends Model
{
    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'amount',
    ];
}
