<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adoption extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id', 'status', 'reason', 'description', 'requirements',
        'contact', 'admin_notes', 'adopted_by', 'adopted_at', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'adopted_at'  => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function adopter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adopted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
