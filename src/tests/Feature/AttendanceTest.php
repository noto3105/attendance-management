<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DatabaseSeeder;
use Laravel\Fortify\Features;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Str;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaking;
Use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //登録処理のテスト
    public function test_register_name()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/register')->post('register', [
            //'name' => 'taro',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('name');
    }

    public function test_register_email()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/register')->post('register', [
            'name' => 'taro',
            //'email' => 'test@example.com',
            'password' => 'password123',
            'password_comfirmation' => 'password123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
    }

    public function test_register_less_password()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/register')->post('/register', [
            'name' => 'taro',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_comfirmation' => 'pass',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
    }

    public function test_register_mismatch_password()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/register')->post('/register', [
            'name' => 'taro',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_comfirmation' => 'password456',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password', 'password_confirmation');
    }

    public function test_register_password()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/register')->post('/register', [
            'name' => 'taro',
            'email' => 'test@example.com',
            // 'password' => 'password123',
            'password_comfirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
    }

    public function test_register_success()
    {
        config(['fortify.features' => [Features::registration()]]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $email = 'test+' . Str::random(6) . '@example.com';

        $response = $this->from('/register')->post('/register', [
            'name' => 'taro',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['email' => $email]);
        
        $response->assertRedirect(route('verification.notice'));
    }

    //ログイン処理のテスト
    public function test_login_email()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('login')->post('login', [
            //'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);        
    }

    public function test_login_password()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('login')->post('login', [
            'email' => 'test@example.com',
            //'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password']);        
    }

    public function test_login_error()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        User::factory()->create([
            'email' => 'test1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from('login')->post('login', [
            'email' => 'test1@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);        
    }

    //管理者ログイン
    public function test_email_admin_login()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/admin_login')->post('/admin_login', [
            //'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin_login');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_password_admin_login()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->from('/admin_login')->post('/admin_login', [
            'email' => 'admin@example.com',
            //'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin_login');
        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_login_error()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $user = User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role'     => 2
        ]);

        $response = $this->from('/admin_login')->post('/admin_login', [
            'email'    => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin_login');
        $response->assertSessionHasErrors(['email']);
    }

    //日時取得機能
    public function test_get_date_and_time()
    {
        $frozen = Carbon::create(2025, 11, 11, 11, 11, 11, 'Asia/Tokyo');
        Carbon::setTestNow($frozen);

        $this->assertSame('2025-11-11 11:11:11', now()->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    //ステータス確認機能
    public function test_status_not_working()
    {
        $path = '/attendance'; 
        $user = User::factory()->create(['role'=> 1]);
        $this->actingAs($user);

        $this->get($path)
            ->assertOk()
            ->assertViewHas('status', 'not_working')
            ->assertSee('勤務外');
    }

    public function test_status_working()
    {
        $path = '/attendance'; 
        $user = User::factory()->create(['role'=>1]);
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => null,
        ]);

        $this->get($path)
            ->assertOk()
            ->assertViewHas('status', 'working')
            ->assertSee('出勤中');
    }

    public function test_status_on_break()
    {
        $path = '/attendance'; 
        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => null,
        ]);

        Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'break_in' => '12:00:00',
            'break_out' =>null,
        ]);

        $this->get($path)
            ->assertOk()
            ->assertViewHas('status', 'on_break')
            ->assertSee('休憩中');
    }

    public function test_status_finished()
    {
        $path = '/attendance'; 
        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $this->get($path)
            ->assertOk()
            ->assertViewHas('status', 'finished')
            ->assertSee('退勤済')
            ->assertSeeText('お疲れ様でした。');
    }

    //出勤機能
    public function test_attendance_button()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $postPath = '/attendance/start';
        $screenPath = '/attendance';

        $response = $this->post($postPath);

        $response->assertStatus(302)->assertRedirect($screenPath);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'end_time' => null,
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => now()->format('H:i:s'),
        ]);

        $screen = $this->get($screenPath);
        $screen->assertOk()
            ->assertViewHas('status', 'working')
            ->assertSee('出勤中');
    }

    public function test_start_only_once()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $postPath = '/attendance/start';
        $screenPath = '/attendance';
        $today = Carbon::today()->toDateString();

        $res1 = $this->post($postPath);
        $res1->assertStatus(302)->assertRedirect($screenPath);

        $first = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        $res2 = $this->post($postPath);
        $res2->assertStatus(302);

        $this->assertSame(
            1,
            Attendance::where('user_id', $user->id)->where('date', $today)->count()
        );

        $after = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        $this->assertEquals($first->id, $after->id);
        $this->assertEquals($first->start_time, $after->start_time);

        $screen = $this->get($screenPath);
        $screen->assertOk()
            ->assertViewHas('status', 'working')
            ->assertSee('出勤中');
    }

    public function test_start_time_display_on_list()
    {
        $listPath = '/list';

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $today = now()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '08:30:00',
            'end_time' => null,
        ]);

        $response = $this->get($listPath);
        $response->assertOk();

        $response->assertSee('08:30');
    }

    public function test_break_in_button()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 12, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' =>null,
        ]);

        $postPath = '/attendance/break_in';
        $screenPath = '/attendance';
        $this->post($postPath)
            ->assertStatus(302)
            ->assertRedirect($screenPath);

        $this->assertDatabaseHas('breakings', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'break_in' => '12:00:00',
            'break_out' => null,
        ]);

        $this->get($screenPath)
            ->assertOk()
            ->assertViewHas('status', 'on_break')
            ->assertSee('休憩中');
    }

    public function test_break_out_button()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 12, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' =>null,
        ]);


        Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'break_in' => '12:00:00',
            'break_out' => null,
        ]);

        $this->travelTo(Carbon::create(2025, 8, 20, 13, 0, 0, 'Asia/Tokyo'));

        $postPath = '/attendance/break_out';
        $screenPath = '/attendance';

        $this->post($postPath)
            ->assertStatus(302)
            ->assertRedirect($screenPath);

        $this->assertDatabaseHas('breakings', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => Carbon::today()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $this->get($screenPath)
            ->assertOk()
            ->assertViewHas('status', 'working')
            ->assertSee('出勤中');

        $this->travelBack();
    }

    //「休憩は一日に何回でもできる」と「休憩戻は一日に何回でもできる」機能
    public function test_can_take_breaks_as_many_times()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(\Illuminate\Support\Carbon::create(2025, 8, 20, 9, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => null,
        ]);

        $postInPath = '/attendance/break_in';
        $postOutPath = '/attendance/break_out';
        $screenPath = '/attendance';
        $today = Carbon::today()->toDateString();

        $breakSets = [
            ['in' => '12:00:00', 'out' => '12:10:00'],
            ['in' => '13:00:00', 'out' => '13:45:00'],
            ['in' => '15:00:00', 'out' => '15:05:00'],
        ];

        foreach ($breakSets as $i => $set) {
            [$h, $m, $s] = array_map('intval', explode(':', $set['in']));
            $this->travelTo(Carbon::create(2025, 8, 20, $h, $m, $s, 'Asia/Tokyo'));

            $this->post($postInPath)->assertStatus(302)->assertRedirect($screenPath);

            $this->assertDatabaseHas('breakings', [
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'date' => Carbon::today()->toDateString(),
                'break_in' => $set['in'],
                'break_out' => null,
            ]);

            $this->get($screenPath)->assertOk()
                ->assertViewHas('status', 'on_break')
                ->assertSee('休憩中');

            [$h, $m, $s] = array_map('intval', explode(':', $set['out']));
            $this->travelTo(Carbon::create(2025, 8, 20, $h, $m, $s, 'Asia/Tokyo'));

            $this->post($postOutPath)->assertStatus(302)->assertRedirect($screenPath);

            $this->assertDatabaseHas('breakings', [
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'date' => Carbon::today()->toDateString(),
                'break_in' => $set['in'],
                'break_out' => $set['out'],
            ]);

            $this->get($screenPath)->assertOk()
                ->assertViewHas('status', 'working')
                ->assertSee('出勤中');

        }

        $this->assertSame(
            count($breakSets),
            Breaking::where('attendance_id', $attendance->id)->count()
        );
    }

    public function test_break_in_time_diplay_on_list()
    {
        $listPath = '/list';

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $today = now()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '08:30:00',
            'end_time' => null,
        ]);

        Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $today,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->get($listPath);
        $response->assertOk();

        $response->assertSee('1:00');
    }

    //退勤機能
    public function test_end_button()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(\Illuminate\Support\Carbon::create(2025, 8, 20, 8, 30, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $today = now()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '08:30:00',
            'end_time' => null,
        ]);

        $this->travelTo(Carbon::create(2025, 8, 20, 17, 30, 0, 'Asia/Tokyo'));

        $postPath = '/attendance/end';
        $screenPath = '/attendance';

        $this->post($postPath)
            ->assertStatus(302)
            ->assertRedirect($screenPath);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $this->get($screenPath)
            ->assertOk()
            ->assertViewHas('status', 'finished')
            ->assertSeeText('お疲れ様でした。');
    }

    public function test_end_time_diplay_on_list()
    {
        $listPath = '/list';

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $today = now()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $response = $this->get($listPath);
        $response->assertOk();

        $response->assertSee('17:30');
    }

    //勤怠一覧情報取得機能
    public function test_attendance_list_show_all_records()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $listPath = '/list';

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $rows = [
            ['date' => '2025-08-18', 'start' => '08:30:00', 'end' => '17:30:00'],
            ['date' => '2025-08-19', 'start' => '09:00:00',  'end' => '18:00:00'],
            ['date' => '2025-08-20', 'start' => '10:15:00', 'end' => null],
        ];

        foreach ($rows as $r) {
        \App\Models\Attendance::create([
            'user_id'    => $user->id,
            'date'       => $r['date'],
            'start_time' => $r['start'],
            'end_time'   => $r['end'],
        ]);
        }

        $response = $this->get($listPath);
        $response->assertOk();

        foreach ($rows as $r) {
            $response->assertSee(substr($r['start'], 0, 5));
        }

        $this->travelBack();
    }

    public function test_attendance_list_show_current_month()
    {
        $listPath= '/list';

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = \App\Models\User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $response = $this->get($listPath);
        $response->assertOk();

        $candidates = [
            now()->format('Y/m'),
        ];

        $html = $response->getContent();
        $found = false;
        foreach ($candidates as $text) {
            if (str_contains($html, $text)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue(
            $found,
            '現在の月の表示が見つかりませんでした。候補: '.implode(', ', $candidates)
        );
        $this->travelBack();
    }

    public function test_attendance_list_show_prev_month()
    {
        $listBasePath = '/list';

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $prevMonth = now()->subMonthNoOverflow();
        $prevDate = $prevMonth->copy()->day(10)->toDateString();
        $currentDate = now()->copy()->day(10)->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $prevDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date'  => $currentDate,
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $response = $this->get($listBasePath.'?month='.$prevMonth->format('Y-m'));
        $response->assertOk();

        $response->assertSee($prevMonth->format('Y/m'));
        $response->assertDontSee(now()->format('Y/m'));

        $this->travelBack();
    }

    public function test_attendance_list_show_next_month()
    {
        $listBasePath = '/list';

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $nextMonth = now()->addMonthNoOverflow();
        $nextDate = $nextMonth->copy()->day(10)->toDateString();
        $currentDate = now()->copy()->day(10)->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',        
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date'  => $currentDate,
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $response = $this->get($listBasePath.'?month='.$nextMonth->format('Y-m'));
        $response->assertOk();

        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertDontSee(now()->format('Y/m'));

        $this->travelBack();
    }

    public function test_transition_detail_screen()
    {
        $listPath = '/list';

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(\Illuminate\Support\Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => null,
        ]);

        $response = $this->get($listPath);
        $response->assertOk();

        $detailPath = '/attendance/'.$attendance->id;
        $response->assertSee($detailPath);
        $response->assertSee('詳細');

        $response = $this->get($detailPath);
        $response->assertOk()
            ->assertViewHas('attendance.id', $attendance->id)
            ->assertSee('勤怠詳細');

        $this->travelBack();
    }

    //勤怠詳細情報取得機能
    public function test_detail_show_user_name()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create([
            'role' => 1,
            'name' => '山田太郎',
        ]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => null,
        ]);
        
        $detailPath = '/attendance/'.$attendance->id;

        $response = $this->get($detailPath);
        $response->assertOk()
            ->assertViewHas('attendance.id', $attendance->id)
            ->assertSee($user->name);

        $this->travelBack();
    }

    public function test_detail_show_select_date()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $selectedDate = '2025-08-20';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $selectedDate,
            'start_time' => '09:00:00',
            'end_time' => null,
        ]);
        
        $detailPath = '/attendance/'.$attendance->id;

        $response = $this->get($detailPath);
        $response->assertOk()
            ->assertViewHas('attendance.id', $attendance->id)
            ->assertSee('2025年')
            ->assertSee('8月20日');

        $this->travelBack();
    }

    public function test_detail_show_correct_time()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
        
        $detailPath = '/attendance/'.$attendance->id;
        $response = $this->get($detailPath);
        $response->assertOk()
            ->assertSee('09:00')
            ->assertSee('18:00');

        $this->travelBack();
    }

    public function test_detail_show_break_time()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '13:15:00',
            'break_out' => '13:30:00',
        ]);
        
        $detailPath = '/attendance/'.$attendance->id;
        $response = $this->get($detailPath);
        $response->assertOk()
            ->assertSee('12:00')
            ->assertSee('13:00')
            ->assertSee('13:15')
            ->assertSee('13:30');

        $this->travelBack();
    }

    //勤怠詳細情報修正機能
    public function test_start_time_is_later_than_end_time()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '12:30:00',
        ]);

        $detailPath = '/attendance/'.$attendance->id;

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'new_date' => now()->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '09:00:00',
            'comment' => 'test'
        ];

        $response = $this->from($detailPath)->post('/application', $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('start_time');

        $this->travelBack();
    }

    public function test_break_in_is_later_than_end_time()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/attendance/'.$attendance->id;

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'new_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'break_in' => '15:30:00',
            'break_out' => '16:00:00',
            'comment' => 'test',
        ];

        $response = $this->from($detailPath)->post('/application', $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('break_in');

        $this->travelBack();
    }

    public function test_break_out_is_later_than_end_time()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/attendance/'.$attendance->id;

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'new_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'break_in' => '14:45:00',
            'break_out' => '15:15:00',
            'comment' => 'test',
        ];

        $response = $this->from($detailPath)->post('/application', $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('break_out');

        $this->travelBack();
    }

    public function test_comment_does_not_filled()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/attendance/'.$attendance->id;

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'new_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
            //'comment' => 'test',
        ];

        $response = $this->from($detailPath)->post('/application', $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('comment');

        $this->travelBack();

    }

    public function test_correction_application_processing()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);
        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/attendance/'.$attendance->id;

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'new_date' => $workDate,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_in' => '12:00',
            'break_out' => '13:00',
            'comment' => 'test',
        ];

        $response = $this->post('/application', $payload);
        $response->assertStatus(302)->assertRedirect('/list');

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 0,
            'comment' => 'test',
            'requested_date' => $submitDate,
            'new_date' => $workDate,
        ]);

        $application = Application::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($application);
        $this->assertSame('09:00', substr($application->new_start_time, 0, 5));
        $this->assertSame('18:00', substr($application->new_end_time, 0, 5));
        $this->assertSame('12:00', substr($application->new_break_in, 0, 5));
        $this->assertSame('13:00', substr($application->new_break_out, 0, 5));

        $this->travelBack();

    }

    public function test_unapproval_list_show_all_applications()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);
        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 0,
            'new_start_time' => '08:00:00',
            'new_end_time' => '17:00:00',
            'new_break_in' => '12:00:00',
            'new_break_out' => '13:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 0,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_in' => '13:00:00',
            'new_break_out' => '14:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $listPath = '/application_list?tab=unapproved';
        $response = $this->get($listPath);
        $response->assertOk();

        $html = $response->getContent();

        $targetDateText = Carbon::parse($workDate)->format('Y/m/d');
        $this->assertSame(
            2,
            substr_count($html, $targetDateText),
        );

        $this->assertGreaterThanOrEqual(
            2,
            substr_count($html, '承認待ち'),
        );
        $this->assertStringContainsString($user->name, $html);

        $this->travelBack();
    }

    public function test_approval_list_show_approved_applications()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);
        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 1,
            'new_start_time' => '08:00:00',
            'new_end_time' => '17:00:00',
            'new_break_in' => '12:00:00',
            'new_break_out' => '13:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 1,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_in' => '13:00:00',
            'new_break_out' => '14:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $listPath = '/application_list?tab=approved';
        $response = $this->get($listPath);
        $response->assertOk();

        $html = $response->getContent();

        $targetDateText = Carbon::parse($workDate)->format('Y/m/d');
        $this->assertSame(
            2,
            substr_count($html, $targetDateText),
        );

        $this->assertGreaterThanOrEqual(
            2,
            substr_count($html, '承認済み'),
        );
        $this->assertStringContainsString($user->name, $html);

        $this->travelBack();
    }

    public function test_go_to_application_details_screen()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $this->actingAs($user);
        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 0,
            'new_start_time' => '08:00:00',
            'new_end_time' => '17:00:00',
            'new_break_in' => '12:00:00',
            'new_break_out' => '13:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $this->assertDatabaseHas('applications', [
            'id'            => $application->id,
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'approval'      => 0,
        ]);

        $listPath = '/application_list?tab=unapproved';
        $response = $this->get($listPath);
        $response->assertOk()
            ->assertViewHas('applications', function ($applications) use ($application) {
            return $applications->contains('id', $application->id);
        });

        $detailPath = '/attendance/' . $application->attendance->id;
        $html = $response->getContent();

        $this->assertStringContainsString('href="' . $detailPath . '"', $html);
        $this->assertStringContainsString('詳細', $html);

        $detailResponse = $this->get($detailPath);
        $detailResponse->assertOk()
            ->assertViewHas('attendance.id', $attendance->id);

        $this->travelBack();
    }

    //勤怠一覧情報取得機能（管理者）
    public function test_admin_list_show_all_users_records()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user1 = User::factory()->create(['role' => 1]);
        $user2 = User::factory()->create(['role' => 1]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00'
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking1 = Breaking::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $breaking2 = Breaking::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => 2]);
        $this->actingAs($adminUser);

        $listPath = '/admin/attendance_list';
        $response = $this->get($listPath);

        $response->assertOk();

        $response->assertSee(now()->format('Y/m/d'));
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('1:00');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user1->id,
            'date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00'
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user2->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->assertDatabaseHas('breakings', [
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $this->assertDatabaseHas('breakings', [
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
    }

    public function test_admin_list_display_current_date()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $adminUser = User::factory()->create(['role' => 2]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance_list');
        $response->assertOk();
        $response->assertSee(now()->format('Y/m/d'));
    }

    public function test_admin_list_display_previous_day_records()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-19',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-20',
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get('/admin/attendance_list?date=2025-08-19');
        $response->assertOk();

        $response->assertSee('2025/08/19');

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('08:30');
        $response->assertDontSee('17:30');
    }

    public function test_admin_list_diplay_next_day_records()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-21',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-20',
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get('/admin/attendance_list?date=2025-08-21');
        $response->assertOk();

        $response->assertSee('2025/08/21');

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('08:30');
        $response->assertDontSee('17:30');
    }

    //勤怠詳細情報取得・修正機能（管理者）
    public function test_admin_detail_show_selected_data()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/' .$attendance->id);
        $response->assertOk();

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('08:00');
        $response->assertDontSee('17:00');
    }

    public function test_admin_error_start_time_is_later_than_end_time()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '12:30:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);
        $detailPath = '/admin/attendance/'. $attendance->id;

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'date' => now()->toDateString(),
            'start_time' => '12:00:00',
            'end_time' => '09:00:00',
            'comment' => 'test'
        ];

        $response = $this->from($detailPath)->post($detailPath, $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('start_time');

        $this->travelBack();
    }

    public function test_admin_break_in_is_later_than_end_time()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/admin/attendance/'.$attendance->id;
        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'break_in' => '15:30:00',
            'break_out' => '16:00:00',
            'comment' => 'test',
        ];

        $response = $this->from($detailPath)->post($detailPath, $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('break_in');

        $this->travelBack();
    }

    public function test_admin_break_out_is_later_than_end_time()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/admin/attendance/'.$attendance->id;
        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'break_in' => '14:45:00',
            'break_out' => '15:15:00',
            'comment' => 'test',
        ];

        $response = $this->from($detailPath)->post($detailPath, $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('break_out');

        $this->travelBack();
    }

    public function test_admin_comment_not_filled()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => now()->toDateString(),
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $detailPath = '/admin/attendance/'.$attendance->id;
        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $payload = [
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
            //'comment' => 'test',
        ];

        $response = $this->from($detailPath)->post($detailPath, $payload);

        $response->assertStatus(302)
            ->assertRedirect($detailPath);
        $response->assertSessionHasErrors('comment');

        $this->travelBack();
    }

    //ユーザー情報取得機能（管理者）
    public function test_admin_show_all_users_name_and_email()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        User::create([
            'name' => '山田太郎',
            'email' => 'ytaro@example.com',
            'password' => 'taro1234',
            'password_confirmation' => 'taro1234',
            'role' => 1,
        ]);
        User::create([
            'name' => '田中花子',
            'email' => 'hana@example.com',
            'password' => 'hana1234',
            'password_confirmation' => 'hana1234',
            'role' => 1,
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);
        $response = $this->get('/staff_list');
        $response->assertOk();

        $response->assertSee('山田太郎');
        $response->assertSee('ytaro@example.com');
        $response->assertSee('田中花子');
        $response->assertSee('hana@example.com');
    }

    public function test_admin_can_see_staff_attendance_records()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get('/admin/attendance/staff/'. $user->id);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_staff_attendance_show_previous_month()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $listBasePath = '/admin/attendance/staff/'. $user->id;

        $prevMonth = now()->subMonthNoOverflow();
        $prevDate = $prevMonth->copy()->day(10)->toDateString();
        $currentDate = now()->copy()->day(10)->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $prevDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date'  => $currentDate,
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get($listBasePath.'?month='.$prevMonth->format('Y-m'));
        $response->assertOk();

        $response->assertSee($prevMonth->format('Y/m'));
        $response->assertDontSee(now()->format('Y/m'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('08:30');
        $response->assertDontSee('17:30');

        $this->travelBack();
    }

    public function test_staff_attendance_show_next_month()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $listBasePath = '/admin/attendance/staff/'. $user->id;

        $nextMonth = now()->addMonthNoOverflow();
        $nextDate = $nextMonth->copy()->day(10)->toDateString();
        $currentDate = now()->copy()->day(10)->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date'  => $currentDate,
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get($listBasePath.'?month='.$nextMonth->format('Y-m'));
        $response->assertOk();

        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertDontSee(now()->format('Y/m'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('08:30');
        $response->assertDontSee('17:30');

        $this->travelBack();
    }
    
    public function test_transition_admin_detail()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(\Illuminate\Support\Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $listPath = '/admin/attendance/staff/'. $user->id;
        $response = $this->get($listPath);
        $response->assertOk();
        
        $detailPath = '/admin/attendance/'.$attendance->id;
        $response->assertSee($detailPath);
        $response->assertSee('詳細');

        $response = $this->get($detailPath);
        $response = $this->get($detailPath);
        $response->assertOk()
            ->assertViewHas('attendance.id', $attendance->id)
            ->assertSee('勤怠詳細');

        $this->travelBack();
    }

    //勤怠情報修正機能
    public function test_admin_application_list_show_unapproval()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user1 = User::factory()->create(['role' => 1]);
        $user2 = User::factory()->create(['role' => 1]);

        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $workDate,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $breaking1 = Breaking::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $breaking2 = Breaking::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance1->id,
            'date' => $workDate,
            'break_in' => '11:30:00',
            'break_out' => '12:30:00',
        ]);

        $application1 = Application::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'breaking_id' => $breaking1->id,
            'approval' => 0,
            'new_start_time' => '08:00:00',
            'new_end_time' => '17:00:00',
            'new_break_in' => '12:00:00',
            'new_break_out' => '13:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $application2 = Application::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'breaking_id' => $breaking2->id,
            'approval' => 1,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_in' => '13:00:00',
            'new_break_out' => '14:00:00',
            'comment' => 'test2',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $listPath = '/admin/application_list?tab=unapproved';
        $response = $this->get($listPath);
        $response->assertOk();

        $response->assertSee($user1->name);

        $response->assertDontSee($user2->name);

        $this->travelBack();
    }

    public function test_admin_application_list_show_approved()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user1 = User::factory()->create(['role' => 1]);
        $user2 = User::factory()->create(['role' => 1]);

        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $workDate,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $breaking1 = Breaking::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $breaking2 = Breaking::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'date' => $workDate,
            'break_in' => '11:30:00',
            'break_out' => '12:30:00',
        ]);

        $application1 = Application::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'breaking_id' => $breaking1->id,
            'approval' => 0,
            'new_start_time' => '08:00:00',
            'new_end_time' => '17:00:00',
            'new_break_in' => '12:00:00',
            'new_break_out' => '13:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $application2 = Application::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'breaking_id' => $breaking2->id,
            'approval' => 1,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_in' => '13:00:00',
            'new_break_out' => '14:00:00',
            'comment' => 'test2',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $listPath = '/admin/application_list?tab=approved';
        $response = $this->get($listPath);
        $response->assertOk();

        $response->assertSee($user2->name);

        $response->assertDontSee($user1->name);

        $this->travelBack();
    }

    public function test_approval_show_correct_information()
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1,]);
        $submitDate = now()->toDateString();
        $workDate = '2025-08-18';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $workDate,
            'break_in' => '11:30:00',
            'break_out' => '12:30:00',
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 0,
            'new_start_time' => '08:00:00',
            'new_end_time' => '17:00:00',
            'new_break_in' => '12:00:00',
            'new_break_out' => '13:00:00',
            'comment' => 'test',
            'new_date' => $workDate,
            'requested_date' => $submitDate,
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $response = $this->get('/application/approval/'.$application->id);
        $response->assertOk();

        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee($application->comment);
    }

    public function test_admin_approve_aaplication_request()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        config(['app.timezone' => 'Asia/Tokyo']);
        $this->travelTo(Carbon::create(2025, 8, 20, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create(['role' => 1]);

        $workDate = '2025-08-18';
        $requestedDate = now()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $workDate,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $breaking = Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $workDate,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $application = Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => $breaking->id,
            'approval' => 0,
            'new_date' => $workDate,
            'new_start_time' => '08:30:00',
            'new_end_time' => '17:30:00',
            'new_break_in' => '12:15:00',
            'new_break_out' => '12:45:00',
            'comment' => 'request for correction',
            'requested_date' => $requestedDate,
        ]);

        $admin = User::factory()->create(['role' => 2]);
        $this->actingAs($admin);

        $approvalPath = '/application/approval/' . $application->id;

        $response = $this->from($approvalPath)->post($approvalPath, ['approval' => 1]);
    $response->assertStatus(302);

    $this->assertDatabaseHas('applications', [
        'id'       => $application->id,
        'approval' => 1,
    ]);

    $this->assertDatabaseHas('attendances', [
        'id'         => $attendance->id,
        'date'       => $workDate,
        'start_time' => '08:30:00',
        'end_time'   => '17:30:00',
    ]);
    $this->assertDatabaseMissing('attendances', [
        'id'         => $attendance->id,
        'start_time' => '09:00:00',
        'end_time'   => '18:00:00',
    ]);

    $this->assertDatabaseHas('breakings', [
        'id'        => $breaking->id,
        'date'      => $workDate,
        'break_in'  => '12:15:00',
        'break_out' => '12:45:00',
    ]);
    $this->assertDatabaseMissing('breakings', [
        'id'        => $breaking->id,
        'break_in'  => '12:00:00',
        'break_out' => '13:00:00',
    ]);

    $approvedListPath   = '/admin/application_list?tab=approved';

    $response = $this->get($approvedListPath);
    $response->assertOk();
    $response->assertSee($user->name);

    $this->travelBack();
    }
    
    //メール認証機能
    public function test_register_send_verification_mail()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'taro',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_email_verification_link_completes_verification()
    {
        config(['app.url' => 'http://localhost']);

        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        Notification::fake();

        $user->sendEmailverificationNotification();

        $verificationUrl = null;
        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user, &$verificationUrl) {
            $mail = $notification->toMail($user);
            $verificationUrl = $mail->actionUrl;
            return true;
        });

        Event::fake();

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect();
        $this->assertNotNull($user->fresh()->email_verified_at);
        Event::assertDispatched(Verified::class);
    }

    public function test_verified_user_is_redirected_to_attendance_after_email_verification()
    {
        config(['app.url' => 'http://localhost']);

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        Event::fake();
        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);

        Event::assertDispatched(Verified::class);
    }
}