<?php

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => fake()->words(3, true).' Holiday',
            'date' => fake()->date(),
            'affects' => 'all',
            'is_recurring' => false,
            'description' => fake()->sentence(),
        ];
    }
}
