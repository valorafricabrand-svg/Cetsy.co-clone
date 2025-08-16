<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'link',
        'is_read',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties'
    ];
    
    protected $casts = [
        'is_read' => 'boolean',
        'properties' => 'array'
    ];
    
    /**
     * Get the user that this activity belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the subject of the activity
     */
    public function subject()
    {
        return $this->morphTo();
    }
    
    /**
     * Get the causer of the activity
     */
    public function causer()
    {
        return $this->morphTo();
    }
    
    // Removed the problematic scope
}