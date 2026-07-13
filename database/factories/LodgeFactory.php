<?php

namespace Database\Factories;

use App\Modules\Administration\Models\Lodge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lodge>
 */
class LodgeFactory extends Factory
{
    protected $model = Lodge::class;

    public function definition(): array
    {
        $name = fake()->company().' Lodge';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'registration_number' => fake()->optional()->numerify('###'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
        ];
    }
}
