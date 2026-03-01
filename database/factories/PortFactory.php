<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortFactory extends Factory
{
    protected $model = Port::class;

    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'name' => $this->faker->unique()->word(),
        ];
    }
}
