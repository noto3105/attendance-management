<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => '1',
            'date' => '2025-07-01',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-07-02',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-07-03',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-07-04',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-07-05',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-08-01',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-08-02',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-08-03',
            'start_time' => '09:00:00',
            'end_time' => '17:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-09-01',
            'start_time' => '10:00:00',
            'end_time' => '18:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-09-02',
            'start_time' => '10:00:00',
            'end_time' => '18:30:00',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => '1',
            'date' => '2025-09-03',
            'start_time' => '10:00:00',
            'end_time' => '18:30:00',
        ];
        DB::table('attendances')->insert($param);
    }
}
