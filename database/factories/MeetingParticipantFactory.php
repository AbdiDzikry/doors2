<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\User;
use App\Models\ExternalParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeetingParticipant>
 */
class MeetingParticipantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MeetingParticipant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $participantType = $this->faker->randomElement([User::class, ExternalParticipant::class]);
        $participant = $participantType::factory()->create();

        return [
            'meeting_id' => Meeting::factory(),
            'participant_id' => $participant->id,
            'participant_type' => $participantType,
            'status' => $this->faker->randomElement(['pending', 'attended']),
        ];
    }
}
