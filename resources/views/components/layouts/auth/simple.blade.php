<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-transparent antialiased">
        {{ $slot }}
        @fluxScripts
    </body>
</html>
