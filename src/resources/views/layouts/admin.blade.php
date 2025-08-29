<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/common.css')}}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__left">
                <a href="/attendance"><img src="/image/logo.png" alt="coachtechロゴ"></a>
            </div>
            <div class="header-nav">
                <a class="header-nav__btn" href="/admin/attendance_list">勤怠一覧</a>
                <a class="header-nav__btn" href="/staff_list">スタッフ一覧</a>
                <a class="header-nav__btn" href="/admin/application_list">申請一覧</a>
                <form action="/logout" method="post">
                    @csrf
                    <button type="submit" class="header-nav__btn">ログアウト</button>
                </form>
            </div>        
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>