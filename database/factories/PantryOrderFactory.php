<?php

namespace Database\Factories;

use App\Models\PantryOrder;
use App\Models\Meeting;
use App\Models\PantryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PantryOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PantryOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'meeting_id' => Meeting::factory(),
            'pantry_item_id' => PantryItem::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'status' => 'pending',
        ];
    }
}
