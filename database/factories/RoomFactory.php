<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word . ' Room',
            'capacity' => $this->faker->numberBetween(5, 50),
            'description' => $this->faker->sentence,
            'facilities' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['available', 'under_maintenance']),
            'image_path' => 'rooms/' . $this->faker->uuid . '.jpg',
        ];
    }
}
