<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Breaking;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\DetailRequest;

class AttendanceController extends Controller
{
    public function showAttendance(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        $latestBreak = Breaking::where('user_id', $user->id)->where('date', $today)->latest()->first();

        if (!$attendance) {
            $status = 'not_working';
        } elseif ($attendance->end_time) {
            $status = 'finished';
        } elseif ($latestBreak && !$latestBreak->break_out) {
            $status = 'on_break';
        } else {
            $status = 'working';
        }

        return view('attendance', [
            'status' => $status,
        ]);
    }

    public function start(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        if (Attendance::where('user_id', $user->id)->where('date', $today)->exists()) {
            return redirect('/attendance');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now()->format('H:i:s'),
        ]);

        return redirect('/attendance');
    }

    public function end(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($attendance && !$attendance->end_time) {
            $attendance->end_time = Carbon::now()->format('H:i:s');
            $attendance->save();
        }

        return redirect('/attendance');
    }

    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNull('end_time')
            ->latest('id')
            ->first();

        Breaking::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $today,
            'break_in' => Carbon::now()->format('H:i:s'),
        ]);

        return redirect('/attendance');
    }

    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNull('end_time')
            ->latest('id')
            ->first();

        $breaking = Breaking::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_out')
            ->latest()
            ->first();

        if ($breaking) {
            $breaking->break_out = Carbon::now()->format('H:i:s');
            $breaking->save();
        }

        return redirect('/attendance');
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month');
        $currentMonth = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $startOfMonth = $currentMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth = $currentMonth->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        $breakings = Breaking::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $days = [];
        for ($date = $currentMonth->copy()->startOfMonth(); $date->lte($currentMonth->copy()->endOfMonth()); $date->addDay()) {
            $formatted = $date->format('Y-m-d');
            $attendance = $attendances->get($formatted);
            $breaking = $breakings->get($formatted);

            $workDuration = null;
            if ($attendance && $attendance->start_time && $attendance->end_time) {
                $start = Carbon::parse($attendance->start_time);
                $end = Carbon::parse($attendance->start_time);
                $total = $end->diffInMinutes($start);

                if ($breaking && $breaking->break_in && $breaking->break_out) {
                    $breakIn = Carbon::parse($breaking->break_in);
                    $breakOut = Carbon::parse($breaking->break_out);
                    $total -= $breakOut->diffInMinutes($breakIn);
                }
                $workDuration = gmdate("H:1", $total * 60);
            }

            $days[] = [
                'date' => $formatted,
                'attendance' => $attendance,
                'breaking' => $breaking,
                'work_duration' => $workDuration,
            ];
        }

        return view('list', [
            'days' => $days,
            'currentMonth' => $currentMonth->format('Y-m'),
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function showDetail($id)
    {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $breakings = Breaking::where('attendance_id', $attendance->id)
            ->orderBy('break_in')
            ->get();

        $break1 = $breakings->get(0);
        $break2 = $breakings->get(1);
        $extraBreakings = $breakings->slice(2)->values();

        $hasPendingApplication = Application::where('attendance_id', $attendance->id)
            ->where('approval', 0)
            ->exists();       

        return view('detail', compact('user', 'attendance', 'break1', 'break2', 'extraBreakings', 'hasPendingApplication'));
    }

    public function submitCorrection(DetailRequest $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $breaking = null;
        if ($request->filled('breaking_id')) {
            $breaking = Breaking::where('id', $request->breaking_id)
                ->where('attendance_id', $attendance->id)
                ->first();
        }

        Application::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'breaking_id' => optional($breaking)->id,
            'new_date' => $attendance->date,
            'new_start_time' => $request->start_time,
            'new_end_time' => $request->end_time,
            'new_break_in' => $request->break_in,
            'new_break_out' => $request->break_out,
            'comment' => $request->comment,
            'approval' => 0,
            'requested_date' => now()->toDateString(),
        ]);

        return redirect('/list');
    }

    public function applicationList(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'unapproved');

        $query = Application::with('attendance', 'user')
            ->where('user_id', $user->id);

            if ($tab === 'approved') {
                $query->where('approval', 1);
            } else {
                $query->where('approval', 0);
            }

        $applications = $query->get();

        return view('application', compact('applications', 'tab'));
    }
}
