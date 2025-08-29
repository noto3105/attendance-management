<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\DetailRequest;

class AdminController extends Controller
{
    public function adminList(Request $request) {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : Carbon::now()->startOfDay();

        $users = User::all();

        $days = $users->map(function ($user) use ($date) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            $breaking = Breaking::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            $workDuration = null;
            if ($attendance && $attendance->start_time && $attendance->end_time) {
                $start = Carbon::parse($attendance->start_time);
                $end = Carbon::parse($attendance->end_time);
                $diff = $start->diffInMinutes($end);

                $hours = floor($diff / 60);
                $minutes = str_pad($diff % 60, 2, '0', STR_PAD_LEFT);
                $workDuration = "{$hours}:{$minutes}";
            }

            return [
            'user' =>$user,
            'attendance' => $attendance,
            'breaking' => $breaking,
            'work_duration' => $workDuration,
            ];
        });

        return view('admin_list', [
            'days' => $days,
            'currentDate' => $date->format('Y-m-d'),
            'date' => $date,
        ]);
    }

    public function showAdminDetail(Request $request, $id) {
        $attendance = Attendance::with('user')->findOrFail($id);
        $user = $attendance->user;

        $breakings = Breaking::where('attendance_id', $attendance->id)
            ->orderBy('break_in')
            ->get();

        $break1 = $breakings->get(0);
        $break2 = $breakings->get(1);
        $extraBreakings = $breakings->slice(2)->values();

        return view('admin_detail', compact('user', 'attendance', 'break1', 'break2', 'extraBreakings'));
    }

    public function updateAdminDetail(DetailRequest $request, $id) {
        $attendance = Attendance::findOrFail($id);
        
        $attendance->date = $request->input('date');
        $attendance->start_time = $request->input('start_time');
        $attendance->end_time = $request->input('end_time');
        $attendance->comment = $request->input('comment');
        $attendance->save();
    
        $breaking = Breaking::where('attendance_id', $id)->first();
        if ($breaking) {
            $breaking->break_in = $request->input('break_in');
            $breaking->break_out = $request->input('break_out');
            $breaking->save();
        }

        return redirect()->route('admin.attendance.list');
    }

    public function staffList() {
        $users = User::all();

        return view('staff', compact('users'));
    }

    public function adminAttendanceList(Request $request ,$id) {
        $user = User::findOrFail($id);
        $month = $request->query('month');
        $currentMonth = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $startOfMonth = $currentMonth->copy()
        ->startOfMonth()->toDateString();
        $endOfMonth = $currentMonth->copy()->endOfMonth()
        ->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        $breakings = Breaking::where('user_id',$user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

            $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
            $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

            $days = [];
            for ($date = $currentMonth->copy()->startOfMonth();
            $date->lte($currentMonth->copy()->endOfMonth());
            $date->addDay()) {
                $formatted = $date->format('Y-m-d');
                $attendance = $attendances->get($formatted);
                $breaking = $breakings->get($formatted);

                $workDuration = null;
                if ($attendance && $attendance->start_time && $attendance->end_time) {
                    $start = Carbon::parse($attendance->start_time);
                    $end = Carbon::parse($attendance->end_time);
                    $total = $end->diffInMinutes($start);

                    if ($breaking && $breaking->break_in && $breaking->break_out) {
                        $breakIn = Carbon::parse($breaking->break_in);
                        $breakOut = Carbon::parse($breaking->break_out);
                        $total -= $breakOut->diffInMinutes($breakIn);
                    }
                    $hours = floor($total / 60);
                    $minutes = str_pad($total % 60, 2, '0', STR_PAD_LEFT);
                    $workDuration = "{$hours}:{$minutes}";
                }

                $days[] = [
                    'date' => $formatted,
                    'attendance' => $attendance,
                    'breaking' => $breaking,
                    'work_duration' => $workDuration,
                ];
            }

        return view('staff_attendance', [
            'user' => $user,
            'days' => $days,
            'currentMonth' => $currentMonth->format('Y-m'),
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'currentMonthLabel' => $currentMonth->format('Y/m'),
        ]);
    }

        public function adminAttendanceCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

    
        $month = $request->query('month');
        $currentMonth = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $currentMonth->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        $breakings = Breaking::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        $filename = 'attendance_' . $user->id . '_' . $currentMonth->format('Ym') . '.csv';

        return response()->streamDownload(function () use ($currentMonth, $attendances, $breakings) {
            echo "\xEF\xBB\xBF";

            $output = fopen('php://output', 'w');

            fputcsv($output, ['日付', '出勤', '退勤', '休憩', '合計']);

            $date = $currentMonth->copy()->startOfMonth();
            $end  = $currentMonth->copy()->endOfMonth();

            while ($date->lte($end)) {
                $keyDate = $date->format('Y-m-d');

                $attendance = $attendances->get($keyDate);
                $breaking = $breakings->get($keyDate);

                $startTime = ($attendance && $attendance->start_time)
                    ? Carbon::parse($attendance->start_time)->format('H:i') : '';
                $endTime = ($attendance && $attendance->end_time)
                    ? Carbon::parse($attendance->end_time)->format('H:i') : '';

                $breakText = '';
                if ($breaking && $breaking->break_in && $breaking->break_out) {
                    $breakMinutes = Carbon::parse($breaking->break_in)
                        ->diffInMinutes(Carbon::parse($breaking->break_out));
                    $breakText = floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT);
                }

                $workText = '';
                if ($attendance && $attendance->start_time && $attendance->end_time) {
                    $total = Carbon::parse($attendance->end_time)
                        ->diffInMinutes(Carbon::parse($attendance->start_time));
                    if ($breaking && $breaking->break_in && $breaking->break_out) {
                        $total -= Carbon::parse($breaking->break_out)
                            ->diffInMinutes(Carbon::parse($breaking->break_in));
                    }
                    $workText = floor($total / 60) . ':' . str_pad($total % 60, 2, '0', STR_PAD_LEFT);
                }

                $csvDate = $date->format('Y/m/d');

                fputcsv($output, [$csvDate, $startTime, $endTime, $breakText, $workText]);

                $date->addDay();
            }

            fclose($output);

            }, $filename, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    
    }

    public function adminApplicationList(Request $request) {
        $user = User::all();
        $tab = $request->query('tab', 'unapproved');

        $query = Application::with('attendance', 'user');

            if ($tab === 'approved') {
                $query->where('approval', 1);
            } else {
                $query->where('approval', 0);
            }

        $applications = $query->get();

        $applicationsMapped = $applications->map(function ($app) {
            return [
                'user_name' => optional($app->user)->name,
                'status' => $app->approval ? '承認済み' : '承認待ち',
                'date' => optional($app->attendance)->date,
                'comment' => $app->comment,
                'request_date' => $app->created_at,
                'attendance' => $app->attendance,
            ];
        });

        return view('admin_application', [
            'applications' => $applications,
            'tab' => $tab,
        ]);
    }

    public function showApproval(Request $request, $id) {
    
        $application = Application::with('attendance.user', 'breaking')->findOrFail($id);

        $attendance = $application->attendance;

        $breakings = Breaking::where('attendance_id', $attendance->id)
            ->orderBy('break_in')
            ->take(2)
            ->get();
        $break1 = $breakings->get(0);
        $break2 = $breakings->get(1);
        return view('approval', compact('application', 'attendance', 'break1', 'break2'));
    }

    public function approval(Request $request, $id) {
        $application = Application::with(['attendance', 'breaking'])->findOrFail($id);
        
        DB::transaction(function () use ($application) {
            $attendance = $application->attendance;

            if (!is_null($application->new_date)) {
                $attendance->date = $application->new_date;
            }
            if (!is_null($application->new_start_time)) {                    $attendance->start_time = $application->new_start_time;
            }
            if (!is_null($application->new_end_time)) {
                $attendance->end_time = $application->new_end_time;
            }
            $attendance->save();

            if (!is_null($application->new_break_in) || !is_null($application->new_break_out)) {
                $breaking = $application->breaking;

                if (!$breaking) {
                    $breaking = new Breaking();
                    $breaking->user_id = $application->user_id;
                    $breaking->attendance_id = $attendance->id;
                    $breaking->date = $application->new_date ?? $attendance->date;
                }

                if (!is_null($application->new_break_in)) {
                $breaking->break_in = $application->new_break_in;
                }
                if (!is_null($application->new_break_out)) {
                    $breaking->break_out = $application->new_break_out;
                }
                $breaking->save();

                if (property_exists($application, 'breaking_id') && is_null($application->breaking_id)) {
                    $application->breaking_id = $breaking->id;
                }
            }

            $application->approval = 1;
            $application->save();
        });

        return redirect()->route('admin.applications.approval.show', compact('id'));
    }
}
