<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2025, 2030);

        return [
            'institution_id' => Institution::factory(),
            'name' => "{$year}/".($year + 1).' Academic Year',
            'code' => "AY-{$year}",
            'starts_on' => "{$year}-01-01",
            'ends_on' => "{$year}-12-31",
            'is_current' => false,
            'status' => 'active',
            'description' => fake()->sentence(),
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
            'status' => 'active',
        ]);
    }
}
