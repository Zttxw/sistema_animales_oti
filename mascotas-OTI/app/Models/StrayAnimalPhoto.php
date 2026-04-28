<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Models\StrayAnimal|null $strayAnimal
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimalPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimalPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimalPhoto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimalPhoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimalPhoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StrayAnimalPhoto whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StrayAnimalPhoto extends Model
{
    public $timestamps = false;

    protected $fillable = ['stray_animal_id', 'path'];

    public function strayAnimal(): BelongsTo
    {
        return $this->belongsTo(StrayAnimal::class);
    }
}