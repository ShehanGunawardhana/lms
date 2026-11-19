<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Term>
 */
class TermFactory extends Factory
{
    protected $model = Term::class;

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
            'name' => 'Term '.fake()->numberBetween(1, 3),
            'starts_on' => now()->startOfMonth()->toDateString(),
            'ends_on' => now()->addMonths(3)->endOfMonth()->toDateString(),
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }
}
