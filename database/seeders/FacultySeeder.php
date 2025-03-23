<?php
namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            ['name' => 'Faculty of Arts'],
            ['name' => 'Faculty of Science'],
            ['name' => 'Faculty of Engineering'],
            ['name' => 'Faculty of Medicine'],
            ['name' => 'Faculty of Law'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::create($faculty);
        }
    }
}