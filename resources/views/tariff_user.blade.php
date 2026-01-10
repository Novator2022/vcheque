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
        <div class="item" style="padding: .78571429em .92857143em;">
            <h2>Ваш тариф: {{ Auth::user()->procent ?? $default }}%</h2>
        </div>
    </body>
</html>
