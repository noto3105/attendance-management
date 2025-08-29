@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="login__content">
        <div class="login-form__heading">
            <h1 class="login-form__heading-title">管理者ログイン</h1>
        </div>
        <form class="login-form" action="/admin_login" method="post">
            @csrf
            <div class="form__group">
                <div class="form__group-title">
                    <p class="form__label--item">メールアドレス</p>
                </div>
                <div>
                    <div class="form__input--text">
                        <input type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="form__error">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <p class="form__label--item">パスワード</p>
                </div>
                <div class="form__input--text">
                   <input type="password" name="password">
                </div>
               <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">管理者ログインする</button>
            </div>
        </form>
    </div>
</div>
@endsection