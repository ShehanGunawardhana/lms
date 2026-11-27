<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InstitutionType;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 9999),
            'type' => InstitutionType::Mixed,
            'logo_file_id' => null,
            'timezone' => 'UTC',
            'locale' => 'en',
            'status' => 'active',
        ];
    }
}
