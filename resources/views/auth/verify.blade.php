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
        <div class="card-header">{{ __('Подтвердите вашу электронную почту') }}</div>
        <br/>
        <br/>

        <div class="card-body">
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    <b>{{ __('Новая ссылка отправлена на вашу почту') }}</b>
                </div>
                <br/>
                <br/>
            @endif

            Пожалуйста, проверьте вашу электронную почту и пройдите по полученной в письме ссылке.<br/><br/>
            Не забудьте проверить папку спам.<br/><br/>
            Если вы не получили письмо, то <a href="{{ route('verification.resend') }}">нажмите здесь</a>, чтобы повторно запросить письмо.<br/>

            <br/>
            <br/>

            <a class="nav-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                Выход
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

</div>

@endsection