<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id', 'vaccine_id', 'vaccine_name', 'applied_at',
        'next_dose_at', 'notes', 'file_path', 'registered_by', 'campaign_id',
    ];

    protected $casts = [
        'applied_at'   => 'date',
        'next_dose_at' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(VaccineCatalog::class, 'vaccine_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
