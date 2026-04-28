<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_type_id', 'title', 'content', 'author_id', 'status',
    ];

    public function postType(): BelongsTo
    {
        return $this->belongsTo(PostType::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PostPhoto::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function lostNotice(): HasOne
    {
        return $this->hasOne(LostNotice::class);
    }
}
