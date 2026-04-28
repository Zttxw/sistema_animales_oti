<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Animal> $animals
 * @property-read int|null $animals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Breed> $breeds
 * @property-read int|null $breeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VaccineCatalog> $vaccineCatalog
 * @property-read int|null $vaccine_catalog_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Species whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Species extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function breeds(): HasMany
    {
        return $this->hasMany(Breed::class);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    public function vaccineCatalog(): HasMany
    {
        return $this->hasMany(VaccineCatalog::class);
    }
}