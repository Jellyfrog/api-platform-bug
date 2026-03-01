<?php

namespace Tests\Feature;

use ApiPlatform\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reproduction for API Platform bug: ModelMetadata::getAttributes() drops
 * primary key columns that share a name with HasMany foreign keys.
 *
 * Setup:
 *   - Device model with primary key `device_id`
 *   - Port model with foreign key `device_id` referencing devices
 *   - Device has a HasMany relation to Port
 *
 * The bug: ModelMetadata::getAttributes() collects ALL foreign key names
 * from discovered relations and excludes matching columns from the attribute
 * list. For HasMany relations, getForeignKeyName() returns the FK column on
 * the RELATED table (ports.device_id), not on the current model's table.
 * Only BelongsTo foreign keys are local columns that should be excluded.
 *
 * When the primary key name matches a HasMany FK name (devices.device_id
 * matches ports.device_id), the primary key is incorrectly excluded.
 */
class ApiPlatformBugTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The primary key (device_id) should appear in the property name collection.
     *
     * This is the most direct test of the bug — it checks the metadata layer
     * without going through HTTP routing or serialization.
     */
    public function test_primary_key_present_in_property_collection(): void
    {
        $factory = app(PropertyNameCollectionFactoryInterface::class);
        $properties = iterator_to_array($factory->create(Device::class));

        $this->assertContains(
            'device_id',
            $properties,
            'Primary key "device_id" was dropped from the property name collection. '
            . 'ModelMetadata::getAttributes() excludes columns matching foreign key '
            . 'names from ALL relation types, but only BelongsTo foreign keys are '
            . 'local columns. HasMany FK names reference the related table and should '
            . 'not be excluded from the current model.'
        );
    }

    /**
     * GET /api/devices/{id} should return 200 for an existing device.
     *
     * Because the primary key is dropped, API Platform cannot discover the
     * identifier for Device. The Get route is never registered, so this
     * returns 404 instead of 200.
     */
    public function test_get_device_returns_200(): void
    {
        $device = Device::factory()->create();

        $response = $this->get(
            '/api/devices/' . $device->device_id,
            ['Accept' => 'application/ld+json']
        );

        $response->assertStatus(200);
    }

    /**
     * GET /api/devices should return items with proper identifiers.
     *
     * The collection endpoint itself returns 200, but without the primary
     * key in the property collection, items cannot have IRIs generated.
     * The Hydra totalItems field is missing because serialization of
     * individual items fails silently.
     */
    public function test_list_devices_returns_items_with_identifiers(): void
    {
        Device::factory()->count(2)->create();

        $response = $this->get('/api/devices', ['Accept' => 'application/ld+json']);

        $response->assertStatus(200)
            ->assertJsonPath('totalItems', 2);
    }
}
