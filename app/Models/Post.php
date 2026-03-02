<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
)]
class Post extends Model
{
    protected $fillable = ['title'];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
