@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_application.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="header__container">
        <h1 class="header__logo">申請一覧</h1>
    </div>
    <div class="tab-list___container">
        <div class="tab-list">
            <div class="tab-list__item">
                <a href="/admin/application_list?tab=unapproved" class="tab-list__unapproved {{ request('tab') === 'unapproved' ? 'active' : '' }}">承認待ち</a>
            </div>
            <div class="tab-list__item">
                <a href="/admin/application_list?tab=approved" class="tab-list__approved {{ request('tab') === 'approved' ? 'active' : '' }}">承認済み</a>
            </div>
        </div>
    </div>
    <table class="application_table">
        <tr class="table_head">
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
        @foreach ($applications as $application)
        <tr>
            <td>
                {{
                    $application->approval === 1 ? '承認済み' : '承認待ち'
                }}
            </td>
            <td>{{ $application->user->name }}</td>
            <td>{{ \Carbon\Carbon::parse(optional($application->attendance)->date)->format('Y/m/d') }}</td>
            <td>{{ $application->comment }}</td>
            <td>{{ \Carbon\Carbon::parse($application->created_at)->format('Y/m/d') }}</td>
            <td>
                @if($application->attendance)
                <a href="/application/approval/{{$application->id}}" class="attendance__detail-link">詳細</a>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection