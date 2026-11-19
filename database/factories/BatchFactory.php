<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Batch '.fake()->word(),
            'code' => 'BAT-'.fake()->unique()->numerify('###'),
            'kind' => 'institute_batch',
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
