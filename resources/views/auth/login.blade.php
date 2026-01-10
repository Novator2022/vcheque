@extends('layouts.app')
@section('content')
<style type="text/css">
    body {
      background-color: #fefefe;
    }
    body > .grid {
      height: 100%;
    }
    .image {
      margin-top: -100px;
    }
    .column {
      width: 450px;
    }
</style>
<div class="ui middle aligned center aligned grid">
    <!-- particles -->
    <div id="particles-js" style="height:100%;width:100%;position:absolute;"></div>
    <div class="ui column" style="width:450px;">
        <h2 class="ui teal huge image header">
            <!-- <img src="/logo.png" class="image" width="300px"> -->
            <div class="content">
                Вход
            </div>
        </h2>

        @if ($errors->has('verification'))
            <div class="ui negotive message">
                {{ $errors->first('verification') }}
            </div>
        @endif
        <!-- <form class="ui large form" method="POST" action="{{ route('login') }}"> -->
        <form class="ui large form" method="POST" action="/login">
            {{ csrf_field() }}
            <div class="ui stacked segment">
                <div class="field">
                    <div class="ui left icon input">
                        <i class="mail icon"></i>
                        <input type="text" placeholder="Email адрес" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>
                @if ($errors->has('email'))
                    <div class="ui negotive message">
                        {{ $errors->first('email') }}
                    </div>
                @endif
                <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="password" placeholder="Пароль"  required>
                    </div>
                </div>
                @if ($errors->has('password'))
                    <div class="ui negotive message">
                        {{ $errors->first('password') }}
                    </div>
                @endif
                <button type="submit" class="ui fluid large teal submit button">Войти</buttom>
            </div>
            <div class="ui error message"></div>
        </form>
        <div class="ui message">
            Забыли пароль? <a href="/password/reset">Сбросить</a>
        </div>
        <div class="ui message">
            Не зарегистрированы? <a href="/register">Регистрация</a>
        </div>
    </div>

</div>

@endsection
