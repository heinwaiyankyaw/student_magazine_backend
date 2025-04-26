<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Contribution;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            FacultySeeder::class,
            UserSeeder::class,
            ContributionSeeder::class,
            CommentSeeder::class,
        ]);
        Contribution::factory(100)->create();
    }
}
