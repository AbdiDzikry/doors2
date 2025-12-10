<?php

namespace Database\Factories;

use App\Models\RecurringMeeting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RecurringMeetingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecurringMeeting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'ends_at' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
        ];
    }
}