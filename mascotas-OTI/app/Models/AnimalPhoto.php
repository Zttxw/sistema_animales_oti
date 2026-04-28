<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalPhoto extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'animal_id', 'url', 'is_cover',
    ];

    protected $casts = [
        'is_cover'   => 'boolean',
        'created_at' => 'datetime',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
