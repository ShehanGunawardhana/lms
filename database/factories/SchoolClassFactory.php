<?php

namespace Database\Factories;

use App\Enums\ClassKind;
use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

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
            'batch_id' => null,
            'teacher_id' => User::factory(),
            'title' => fake()->words(3, true).' Class',
            'subject' => fake()->word(),
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => fake()->randomFloat(2, 5000, 25000),
            'capacity' => fake()->numberBetween(20, 100),
            'room' => 'Hall '.fake()->numberBetween(100, 500),
            'online_url' => fake()->url(),
            'description' => fake()->paragraph(),
            'status' => 'active',
        ];
    }

    public function nursery(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => ClassKind::NurseryGroup->value,
            'fee_amount' => 0.00,
            'capacity' => 20,
            'room' => 'Rainbow Room',
        ]);
    }
}
