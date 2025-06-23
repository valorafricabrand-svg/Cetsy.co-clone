<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $fillable = ['name', 'description', 'status', 'image'];

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }
}
