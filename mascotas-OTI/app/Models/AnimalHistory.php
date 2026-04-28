<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalHistory extends Model
{
    use HasFactory;

    /**
     * Singular table name per SQL schema.
     */
    protected $table = 'animal_history';

    public $timestamps = false;

    protected $fillable = [
        'animal_id', 'user_id', 'change_type', 'description', 'previous_data',
    ];

    protected $casts = [
        'previous_data' => 'array',
        'created_at'    => 'datetime',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Manually set created_at on creation since we use $timestamps = false.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }
}
