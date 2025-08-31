<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_type_id');
    }
}
