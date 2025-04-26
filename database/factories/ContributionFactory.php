<?php
namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contribution>
 */
class ContributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random date within the past 3 years (current year + 2 previous years)
        $createdAt = Carbon::now()
            ->subYears(rand(0, 2))  // 0, 1, or 2 years back
            ->subDays(rand(0, 365)) // Subtract 0-365 days
            ->subHours(rand(0, 23))
            ->subMinutes(rand(0, 59));

        $userIds = $this->faker->numberBetween(5, 7);

        return [
            'title'        => $this->faker->sentence,
            'description'  => $this->faker->paragraphs(3, true),
            'article_path' => 'articles/' . $this->faker->uuid . '.pdf',
            'image_paths'  => json_encode([
                'images/' . $this->faker->uuid . '.jpg',
                'images/' . $this->faker->uuid . '.jpg',
            ]),
            'user_id'      => $userIds,
            'faculty_id'   => $this->faker->numberBetween(1, 5),
            'status'       => $this->faker->randomElement(['pending', 'reviewed', 'selected', 'rejected']),
            'active_flag'  => $this->faker->boolean(90),
            'created_at'   => $createdAt,
            'updated_at'   => $createdAt,
            'createby'     => $userIds,
            'updateby'     => $userIds,
        ];

    }
}
