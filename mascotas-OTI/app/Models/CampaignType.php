<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Campaign> $campaigns
 * @property-read int|null $campaigns_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CampaignType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CampaignType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'icon', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}