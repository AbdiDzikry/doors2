<?php

namespace Database\Factories;

use App\Models\PriorityGuest;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriorityGuestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PriorityGuest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'level' => $this->faker->numberBetween(1, 5),
        ];
    }
}
