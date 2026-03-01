<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Related model — exists so Device can have a HasMany relation where
 * the foreign key name (ports.device_id) matches Device's primary key.
 */
class Port extends Model
{
    use HasFactory;

    protected $primaryKey = 'port_id';

    protected $fillable = ['device_id', 'name'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
