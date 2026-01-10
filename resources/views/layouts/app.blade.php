<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="/img/logo.png">
    <!--  Vendor files -->
    <link rel="stylesheet" href="/semantic.min.css">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//code-eu1.jivosite.com/widget/VY9ExZv5TP" async></script> 
</head>
<body>
    @yield('content')
</body>
</html>
