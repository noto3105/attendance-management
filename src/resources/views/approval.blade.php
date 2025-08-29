@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail">
    <div class="title__content">
        <h1 class="title__logo">勤怠詳細</h1>
    </div>
    <form class="application__form" action="/application/approval/{{ $application->id }}" method="post">
    @csrf
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <table class="detail__table">
            <tr>
                <th class="left__column">名前</th>
                <th>{{ $attendance->user->name }}</th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <td class="left__column">日付</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
                <td></td>
                <td>
                    {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                    <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}">
                </td>
            </tr>
            <tr>
                <td class="left__column">出勤・退勤</td>
                <td><input type="text" name="new_start_time" value="{{ old('new_start_time', optional($application)->new_start_time ? \Carbon\Carbon::parse($application->new_start_time)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('new_start_time')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
                <td>~</td>
                <td><input type="text" name="new_end_time" value="{{ old('new_end_time', optional($application)->new_end_time ? \Carbon\Carbon::parse($application->new_end_time)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('new_end_time')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
            <tr>
                <td class="left__column">休憩</td>
                <td><input type="text" name="new_break_in" value="{{ old('new_break_in', optional($application)->new_break_in ? \Carbon\Carbon::parse($application->new_break_in)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('new_break_in')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
                <td>~</td>
                <td><input type="text" name="new_break_out" value="{{ old('new_break_out', optional($application)->new_break_out ? \Carbon\Carbon::parse($application->new_break_out)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('new_break_out')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
            <tr>
                <td class="left__column">休憩２</td>
                <td><input type="text" name="new_break2_in" value="{{ old('new_break2_in', $application->new_break2_in ? \Carbon\Carbon::parse($application->new_break2_in)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('new_break2_in')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
                <td>~</td>
                <td><input type="text" name="new_break2_out" value="{{ old('new_break2_out', $application->new_break2_out ? \Carbon\Carbon::parse($application->new_break2_out)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('new_break2_out')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
            <tr>
                <td class="left__column">備考</td>
                <td>
                    <textarea name="comment">{{ old('comment', $application->comment) }}</textarea>
                        <div>
                            @error('comment')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div class="application-btn__container">
            @if ($application->approval) 
                <button class="application-btn__submit application-btn__submit--done" disabled>
                承認済み
                </button>
            @else
                <button class="application-btn__submit" type="submit">承認</button>
            @endif
        </div>
    </form>
</div>
@endsection