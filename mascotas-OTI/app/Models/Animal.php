<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Adoption|null $adoption
 * @property-read \App\Models\Breed|null $breed
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CampaignParticipant> $campaignParticipations
 * @property-read int|null $campaign_participations_count
 * @property-read \App\Models\AnimalPhoto|null $coverPhoto
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HealthProcedure> $healthProcedures
 * @property-read int|null $health_procedures_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnimalHistory> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnimalPhoto> $photos
 * @property-read int|null $photos_count
 * @property-read \App\Models\Species|null $species
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vaccination> $vaccinations
 * @property-read int|null $vaccinations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal forAdoption()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Animal withoutTrashed()
 * @mixin \Eloquent
 */
class Animal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'municipal_code',
        'user_id',
        'species_id',
        'breed_id',
        'name',
        'gender',
        'birth_date',
        'approximate_age',
        'color',
        'size',
        'reproductive_status',
        'distinctive_features',
        'status',
        'notes',
        'death_date',
        'death_reason',
    ];

    protected $casts = [
        'birth_date'  => 'date',
        'death_date'  => 'date',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeForAdoption($query)
    {
        return $query->where('status', 'FOR_ADOPTION');
    }

    // ── Relations ──────────────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AnimalPhoto::class);
    }

    public function coverPhoto(): HasOne
    {
        return $this->hasOne(AnimalPhoto::class)->where('is_cover', true);
    }

    public function history(): HasMany
    {
        return $this->hasMany(AnimalHistory::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function healthProcedures(): HasMany
    {
        return $this->hasMany(HealthProcedure::class);
    }

    public function adoption(): HasOne
    {
        return $this->hasOne(Adoption::class);
    }

    public function campaignParticipations(): HasMany
    {
        return $this->hasMany(CampaignParticipant::class);
    }
}