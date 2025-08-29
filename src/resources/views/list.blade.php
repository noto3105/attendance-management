@extends('layouts.staff')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="header__container">
        <h1 class="header__logo">勤怠一覧</h1>
    </div>
    <div class="monthly__content">
        <form action="/list" method="get" class="month__nav">
            <input type="hidden" name="month" value="{{ $prevMonth }}">
            <button type="submit" class="month__btn">
                <img src="/image/left_arrow.png" alt="前月">前月
            </button>
        </form>

        <div class="month__label">
            {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}
        </div>

        <form action="/list" method="get" class="month__nav">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button type="submit" class="month__btn">
                翌月<img src="/image/right_arrow.png" alt="翌月">
            </button>
        </form>
    </div>

    <table class="attendance_table">
        <tr class="table_head">
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($days as $day)
        <tr class="attendance__data">
            <td>{{ \Carbon\Carbon::parse($day['date'])->format('m/d(D)') }}</td>
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
                    <a href="/attendance/{{ $day['attendance']->id }}" class="attendance__detail-link">詳細</a>
                @endif
            </td>
        </tr>
        @endforeach

    </table>
</div>
@endsection