<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'first_name'         => 'John',
                'last_name'          => 'Doe',
                'email'              => 'admin@gmail.com',
                'role_id'            => 1,
                'password'           => bcrypt('password'),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Jane',
                'last_name'          => 'Smith',
                'email'              => 'manager@gmail.com',
                'role_id'            => 2,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Alice',
                'last_name'          => 'Brown',
                'email'              => 'heinwaiyankyaw2001@gmail.com',
                'role_id'            => 3,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'james',
                'last_name'          => 'martin',
                'email'              => 'jamesmartin@gmail.com',
                'role_id'            => 3,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Robert',
                'last_name'          => 'Johnson',
                'email'              => 'student@gmail.com',
                'role_id'            => 4,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Charlie',
                'last_name'          => 'Davis',
                'email'              => 'charliedavis@gmail.com',
                'role_id'            => 4,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Debbie',
                'last_name'          => 'Clark',
                'email'              => 'debbieclark@gmail.com',
                'role_id'            => 4,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => true,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Emily',
                'last_name'          => 'Davis',
                'email'              => 'guest@gmail.com',
                'role_id'            => 5,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => false,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
            [
                'first_name'         => 'Michael',
                'last_name'          => 'Wilson',
                'email'              => 'michaelwilson@gmail.com',
                'role_id'            => 5,
                'password'           => bcrypt('password'),
                'faculty_id'         => rand(1, 5),
                'is_password_change' => false,
                'is_suspended'       => false,
                'active_flag'        => true,
                'createby'           => 1,
                'updateby'           => 1,
            ],
        ];

        foreach ($data as $user) {
            User::create($user);
        }

    }
}