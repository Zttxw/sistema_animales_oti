<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Breed|null $breed
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StrayAnimalHistory> $history
 * @property-read int|null $history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StrayAnimalPhoto> $photos
 * @property-read int|null $photos_count
 * @property-read \App\Models\User|null $reporter
 * @property-read \App\Models\Species|null $species
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimal withoutTrashed()
 * @mixin \Eloquent
 */
class StrayAnimal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'species_id',
        'breed_id',
        'approx_gender',
        'color',
        'size',
        'location',
        'status',
        'notes',
        'reported_by',
    ];

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['DECEASED', 'RELEASED']);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(StrayAnimalPhoto::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(StrayAnimalHistory::class);
    }
}