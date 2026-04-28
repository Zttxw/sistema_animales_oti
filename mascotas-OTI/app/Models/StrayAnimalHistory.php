<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrayAnimalHistory extends Model
{
    use HasFactory;

    /**
     * Singular table name per SQL schema.
     */
    protected $table = 'stray_animal_history';

    public $timestamps = false;

    protected $fillable = [
        'stray_animal_id', 'new_status', 'description', 'registered_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function strayAnimal(): BelongsTo
    {
        return $this->belongsTo(StrayAnimal::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
