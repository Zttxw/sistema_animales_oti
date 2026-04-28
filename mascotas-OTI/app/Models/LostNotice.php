<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'animal_id', 'lost_at', 'lost_location',
        'description', 'contact', 'status',
    ];

    protected $casts = [
        'lost_at' => 'date',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
