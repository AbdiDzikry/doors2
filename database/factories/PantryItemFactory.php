<?php

namespace Database\Factories;

use App\Models\PantryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PantryItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     * @var string
     */
    protected $model = PantryItem::class;

    /**
     * Define the model's default state.
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'stock' => $this->faker->numberBetween(0, 100),
            'type' => $this->faker->randomElement(['makanan', 'minuman']),
        ];
    }
}
