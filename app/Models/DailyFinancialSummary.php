<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyFinancialSummary extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'opening_balance' => 'decimal:0',
        'total_income' => 'decimal:0',
        'total_expense' => 'decimal:0',
        'closing_balance' => 'decimal:0',
    ];
}
