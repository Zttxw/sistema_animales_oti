<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HealthProcedure> $healthProcedures
 * @property-read int|null $health_procedures_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcedureType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcedureType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcedureType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcedureType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcedureType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcedureType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProcedureType extends Model {
    protected $fillable = ['name', 'description', 'requires_detail', 'active'];
    protected $casts = ['requires_detail' => 'boolean', 'active' => 'boolean'];
    public function healthProcedures(): HasMany { return $this->hasMany(HealthProcedure::class); }
}