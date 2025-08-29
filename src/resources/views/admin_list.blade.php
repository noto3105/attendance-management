@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="head__container">
        <h1 class="head__logo">{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠一覧</h1>
    </div>
    <div class="day-bar__content">
        <form action="/admin/attendance_list" method="get" class="day-bar__before">
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d') }}">
            <button type="submit" class="day-bar__btn">
                <img src="/image/left_arrow.png" alt="前日">前日
            </button>
        </form>
        <div class="day-bar__today">
            {{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}
        </div>
        <form action="/admin/attendance_list" method="get" class="day-bar__next">
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d') }}">
            <button type="submit" class="day-bar__btn">
                翌日<img src="/image/right_arrow.png" alt="翌日">
            </button>
        </form>
    </div>
    <table class="attendance_table">
        <tr class="table__head">
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($days as $day)
        <tr class="attendance__data">            
            <td>{{ $day['user']->name }}</td>
            <td>{{ optional($day['attendance'])->start_time ? \Carbon\Carbon::parse($day['attendance']->start_time)->format('H:i') : '' }}</td>
            <td>{{ optional($day['attendance'])->end_time ? \Carbon\Carbon::parse($day['attendance']->end_time)->format('H:i') : '' }}</td>
            <td>
                @if(optional($day['breaking'])->break_in && optional($day['breaking'])->break_out)
                    @php
                    $breakMinutes = \Carbon\Carbon::parse($day['breaking']->break_in)
                    ->diffInMinutes(\Carbon\Carbon::parse($day['breaking']->break_out));
                    @endphp
                    {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                @endif
            </td>
            <td>{{ $day['work_duration'] ?? '' }}</td>
            <td>
                @if($day['attendance'])
                    <a href="/admin/attendance/{{ $day['attendance']->id }}" class="attendance__detail-link">詳細</a>
                @endif
            </td>
        </tr>
        @endforeach

    </table>
</div>
@endsection