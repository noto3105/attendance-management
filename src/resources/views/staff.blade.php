@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')
<div class="staff__list">
    <div class="staff__title-container">
        <h1 class="staff__title-logo">スタッフ一覧</h1>
    </div>
    <div class="staff__table-wrapper">
        <table class="staff__table">
            <thead>
                <tr class="staff__table-head">
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr class="staff__table-row">
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a class="staff__attendance-link" href="/admin/attendance/staff/{{ $user->id }}">
                            詳細
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection