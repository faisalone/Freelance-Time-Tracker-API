<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeLog>
 */
class TimeLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-30 days', 'now');
        $endTime = fake()->boolean(90) 
            ? fake()->dateTimeBetween($startTime, $startTime->format('Y-m-d 23:59:59'))
            : null;

        $hours = $endTime ? round(($endTime->getTimestamp() - $startTime->getTimestamp()) / 3600, 2) : null;

        return [
            'project_id' => Project::factory(),
            'description' => fake()->sentence(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours' => $hours,
            'is_billable' => fake()->boolean(80),
            'tags' => fake()->randomElements(['development', 'design', 'testing', 'meeting', 'research', 'documentation'], fake()->numberBetween(0, 3)),
            'created_at' => $startTime,
        ];
    }

    /**
     * Indicate that the time log is currently running.
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_time' => null,
            'hours' => null,
        ]);
    }

    /**
     * Indicate that the time log is completed.
     */
    public function completed(): static
    {
        $startTime = fake()->dateTimeBetween('-30 days', 'now');
        $endTime = fake()->dateTimeBetween($startTime, $startTime->format('Y-m-d 23:59:59'));
        
        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours' => round(($endTime->getTimestamp() - $startTime->getTimestamp()) / 3600, 2),
        ]);
    }

    /**
     * Set specific working hours.
     */
    public function withHours(float $hours): static
    {
        $startTime = fake()->dateTimeBetween('-30 days', 'now');
        $endTime = (clone $startTime)->modify("+{$hours} hours");
        
        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours' => $hours,
        ]);
    }

    /**
     * Set time log for today.
     */
    public function today(): static
    {
        $startTime = now()->setTime(fake()->numberBetween(8, 12), fake()->numberBetween(0, 59));
        $endTime = fake()->boolean(80) 
            ? $startTime->copy()->addHours(fake()->numberBetween(1, 8))
            : null;

        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours' => $endTime ? round($endTime->diffInMinutes($startTime) / 60, 2) : null,
            'created_at' => $startTime,
        ]);
    }
}
