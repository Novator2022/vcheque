<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ config('app.name', 'VCheck') }}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" sizes="16x16" href="/img/logo.png">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="{{ mix('react/app.css') }}" rel="stylesheet" type="text/css">
        @include('_inline_styles')
    </head>
    <body>
        <div class="ui segment">
            <div class="item selected-editing">
                <div class="content">
                    <div class="description">
                        @if (session('message'))
                            <div class="ui green message">{{ session('message') }}</div>
                        @endif

                        <form class="ui form" method="post" action="/home/tariff/frame">
                            @csrf

                            <div class="field">
                                <label>Процент по умолчанию</label>
                                <div class="ui input"><input name="param[default_procent]" type="text" value="{{ $default }}"></div>
                            </div>

                            <div class="field">
                                <div class="item"><button class="ui green icon button"><i aria-hidden="true" class="pencil icon"></i>Применить</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
