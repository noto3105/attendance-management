<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => Hash::make('test1234'),
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin1234'),
            'role' => '2',
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);

    }
}
