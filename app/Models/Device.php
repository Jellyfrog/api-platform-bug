<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Minimal model to reproduce API Platform bug where ModelMetadata::getAttributes()
 * drops primary key columns that share a name with HasMany foreign keys.
 *
 * Uses a custom primary key (device_id) which is also the foreign key
 * name on the related ports table — a very common Eloquent convention.
 */
#[ApiResource(
    shortName: 'Device',
    operations: [
        new GetCollection(),
        new Get(),
    ],
)]
class Device extends Model
{
    use HasFactory;

    protected $primaryKey = 'device_id';

    protected $fillable = ['hostname'];

    public function ports(): HasMany
    {
        return $this->hasMany(Port::class, 'device_id', 'device_id');
    }
}
