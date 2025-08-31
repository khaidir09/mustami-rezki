<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TailorTransactionItem extends Model
{
    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}
