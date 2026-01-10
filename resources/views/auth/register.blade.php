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
                Регистрация
            </div>
        </h2>

        @if (isset($message))
            <div class="ui negotive message">
                {{ $message }}
            </div>
        @endif

        <form class="ui large form" method="POST" action="{{ route('register') }}">
            {{ csrf_field() }}
            <div class="ui stacked segment">
                <div class="field">
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" placeholder="Ваше имя" name="name" value="{{ old('name') }}" required>
                    </div>
                </div>
                @if ($errors->has('name'))
                    <div class="ui negotive message">
                        {{ $errors->first('name') }}
                    </div>
                @endif
                
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
                        <i class="phone icon"></i>
                        <input type="text" placeholder="Телефон" name="phone" value="{{ old('phone') }}" required>
                    </div>
                </div>
                @if ($errors->has('phone'))
                    <div class="ui negotive message">
                        {{ $errors->first('phone') }}
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

                <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="password_confirmation" placeholder="Подтверждение" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="ui fluid large teal submit button">Регистрация</buttom>
            </div>
            <div class="ui error message"></div>
        </form>

        <div class="ui message">
            Уже зарегистрированы? <a href="/login">Вход</a>
        </div>
    </div>

</div>

@endsection
