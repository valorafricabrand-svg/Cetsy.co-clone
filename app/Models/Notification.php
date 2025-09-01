<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    // Specify the table name to match your migration
    protected $table = 'admin_notifications';
    
    protected $fillable = [
        'user_id',
        'description',
        'is_read'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
    ];
}