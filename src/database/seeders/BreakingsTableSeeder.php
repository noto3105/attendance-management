<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreakingsTableSeeder extends Seeder
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
            'attendance_id' => '1',
            'date' => '2025-07-01',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '2',
            'date' => '2025-07-02',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '3',
            'date' => '2025-07-03',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '4',
            'date' => '2025-07-04',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '5',
            'date' => '2025-07-05',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '6',
            'date' => '2025-08-01',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '7',
            'date' => '2025-08-02',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '8',
            'date' => '2025-08-03',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '9',
            'date' => '2025-09-01',
            'break_in' => '12:30:00',
            'break_out' => '13:30:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '10',
            'date' => '2025-09-02',
            'break_in' => '13:00:00',
            'break_out' => '13:30:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '11',
            'date' => '2025-09-03',
            'break_in' => '12:30:00',
            'break_out' => '13:30:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '11',
            'date' => '2025-09-03',
            'break_in' => '14:30:00',
            'break_out' => '14:45:00',
        ];
        DB::table('breakings')->insert($param);

        $param = [
            'user_id' => '1',
            'attendance_id' => '11',
            'date' => '2025-09-03',
            'break_in' => '15:00:00',
            'break_out' => '15:10:00',
        ];
        DB::table('breakings')->insert($param);
    }
}
