@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/verify.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="verify__content">
        <p class="verify__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        <div class="verify__actions">
            <form class="verify__action-form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify__btn--primary">認証はこちらから</button>
            </form>

            <form class="verify__resend-form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify__link--sub">認証メールを再送する</button>
            </form>
        </div>
    </div>
</div>
@endsection