<?php
namespace Database\Seeders;

use App\Models\Comment;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker    = Factory::create();
        $comments = [];

        for ($i = 0; $i < 10; $i++) {
            $userId     = $faker->numberBetween(3, 4);
            $comments[] = [
                'comment'         => $faker->sentence,
                'user_id'         => $userId,
                'contribution_id' => $faker->numberBetween(1, 10),
                'active_flag'     => 1,
                'createby'        => $userId,
                'updateby'        => $userId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        foreach ($comments as $comment) {
            Comment::create($comment);
        }

    }
}