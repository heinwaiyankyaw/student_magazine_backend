<?php
namespace Database\Seeders;

use App\Models\Contribution;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ContributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker         = Factory::create();
        $contributions = [];

        for ($i = 0; $i < 10; $i++) {
            $userId          = $faker->numberBetween(5, 7);
            $contributions[] = [
                'title'        => $faker->sentence(2),
                'description'  => $faker->paragraph,
                'article_path' => $faker->url,
                'image_paths'  => json_encode([$faker->imageUrl, $faker->imageUrl]),
                'user_id'      => $userId,
                'faculty_id'   => $faker->numberBetween(1, 5),
                'status'       => $faker->randomElement(['pending', 'reviewed', 'selected', 'rejected']),
                'active_flag'  => $faker->boolean,
                'createby'     => $userId,
                'updateby'     => $userId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        foreach ($contributions as $contribution) {
            Contribution::create($contribution);
        }
    }
}
