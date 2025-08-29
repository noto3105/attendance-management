@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/register.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="register__content">
        <div class="register-form__heading">
            <h1 class="register-form__heading-title">会員登録</h1>
        </div>
        <form class="regster-form" action="/register" method="post">
            @csrf
            <div class="form__group">
                <div class="form__group-title">
                    <p class="form__label--item">ユーザー名</p>
                </div>
                <div>
                    <div class="form__input--text">
                        <input type="name" name="name" value="{{ old('name') }}">
                    </div>
                    <div class="form__error">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
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
            <div class="form__group">
                <div class="form__group-title">
                    <p class="form__label--item">確認用パスワード</p>
                </div>
                <div class="form__input--text">
                    <input type="password" name="password_confirmation">
                </div>
                <div class="form__error">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">登録する</button>
            </div>
        </form>
        <div class="login__link">
            <a class="login__button-submit" href="/login">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection