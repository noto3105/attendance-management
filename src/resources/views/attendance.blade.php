@extends('layouts.staff')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="container__text-center">
    <div class="mb-2">
        @switch($status)
            @case('not_working')
                <span class="not-working">勤務外</span>
                @break
            @case('working')
                <span class="working">出勤中</span>
                @break
            @case('on_break')
                <span class="breaking">休憩中</span>
                @break
            @case('finished')
                <span class="finished">退勤済</span>
                @break
        @endswitch
    </div>

    <div class="day">
        <p>{{ \Carbon\Carbon::now()->isoFormat('YYYY年M月D日 (ddd)') }}</p>
    </div>
    <div class="time__container">
        <h1 class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</h1>
    </div>

    @switch($status)
        @case('not_working')
            <form method="POST" action="/attendance/start">
                @csrf
                <button type="submit" class="start__btn">出勤</button>
            </form>
            @break

        @case('working')
            <div class="working__container">
                <form method="POST" action="/attendance/end">
                    @csrf
                    <button type="submit" class="end__btn">退勤</button>
                </form>
                <form method="POST" action="/attendance/break_in">
                    @csrf
                    <button type="submit" class="break-in__btn">休憩入</button>
                </form>
            </div>
            @break

        @case('on_break')
            <form method="POST" action="/attendance/break_out">
                @csrf
                <button type="submit" class="break-out__btn">休憩戻</button>
            </form>
            @break

        @case('finished')
            <p class="finished__text">お疲れ様でした。</p>
            @break
    @endswitch
</div>
@endsection