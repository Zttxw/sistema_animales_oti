<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaccineCatalog extends Model
{
    use HasFactory;

    /**
     * Singular table name per SQL schema.
     */
    protected $table = 'vaccine_catalog';

    protected $fillable = [
        'name', 'description', 'species_id', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class, 'vaccine_id');
    }
}
