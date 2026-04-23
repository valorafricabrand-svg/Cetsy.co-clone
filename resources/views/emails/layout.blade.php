<!DOCTYPE html>
<html lang="{{ locale_html_code() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; background: #f5f7fa;">
    @yield('content')
</body>
</html>
