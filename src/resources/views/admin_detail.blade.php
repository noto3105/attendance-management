@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail">
    <div class="title__content">
        <h1 class="title__logo">勤怠詳細</h1>
    </div>
    <form class="application__form" action="/admin/attendance/{{ $attendance->id }}" method="post">
    @csrf
    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
    <input type="hidden" name="breaking_id" value="{{ optional($break1)->id }}">
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
                <td><input type="text" name="start_time" value="{{ old('start_time', optional($attendance)->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('start_time')
                        {{ $message }}
                        @enderror
                    </div>
                </td>
                <td>~</td>
                <td><input type="text" name="end_time" value="{{ old('end_time', optional($attendance)->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('end_time')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
            <tr>
                <td class="left__column">休憩</td>
                <td><input type="text" name="break_in" value="{{ old('break_in', optional($break1)->break_in ? \Carbon\Carbon::parse($break1->break_in)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('break_in')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
                <td>~</td>
                <td><input type="text" name="break_out" value="{{ old('break_out', optional($break1)->break_out ? \Carbon\Carbon::parse($break1->break_out)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('break_out')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
            <tr>
                <td class="left__column">休憩２</td>
                <td><input type="text" name="break_in2" value="{{ old('break_in2', optional($break2)->break_in ? \Carbon\Carbon::parse($break2->break_in)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('break_in2')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
                <td>~</td>
                <td><input type="text" name="break_out2" value="{{ old('break_out2', optional($break2)->break_out ? \Carbon\Carbon::parse($break2->break_out)->format('H:i') : '') }}">
                    <div class="application__error">
                        @error('break_out2')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
            @foreach ($extraBreakings as $idx => $br)
                @php $n = $idx + 3; @endphp
                <tr>
                    <td class="left_column">休憩{{ $n }}</td>
                    <td>
                        <input type="text" name="break_in{{ $n }}" value="{{ old('break_in' .$n, optional($br)->break_in ? \Carbon\Carbon::parse($br->break_in)->format('H:i') : '' ) }}">
                        <div class="application__error">
                            @error('break_in' .$n)
                            {{ $message }}
                            @enderror
                        </div> 
                    </td>
                    <td>~</td>
                    <td>
                        <input type="text" name="break_out{{ $n }}" value="{{ old('break_out' . $n, optional($br)->break_out ? \Carbon\Carbon::parse($br->break_out)->format('H:i') : '' ) }}">
                        <div class="application__error">
                            @error('break_out' .$n)
                            {{ $message }}
                            @enderror
                        </div> 
                    </td>
                </tr>
            @endforeach
            <tr>
                <td class="left__column">備考</td>
                <td><textarea name="comment"></textarea>
                    <div class="application__error">
                        @error('comment')
                        {{ $message }}
                        @enderror
                    </div>            
                </td>
            </tr>
        </table>
        <div class="application-btn__container">
            <button class="application-btn__submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection