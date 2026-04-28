<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Models\Animal|null $animal
 * @property-read \App\Models\Campaign|null $campaign
 * @property-read \App\Models\ProcedureType|null $procedureType
 * @property-read \App\Models\User|null $registeredBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthProcedure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthProcedure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthProcedure query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthProcedure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthProcedure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HealthProcedure whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HealthProcedure extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'animal_id',
        'procedure_type_id',
        'type_detail',
        'performed_at',
        'description',
        'notes',
        'file_url',
        'registered_by',
        'campaign_id',
    ];

    protected $casts = ['performed_at' => 'date'];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function procedureType(): BelongsTo
    {
        return $this->belongsTo(ProcedureType::class);
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