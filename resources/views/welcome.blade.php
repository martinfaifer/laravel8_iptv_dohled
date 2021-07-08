<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- csrf kontrola formu --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="shortcut icon" href="icon/favicon.ico" type="image/x-icon">
        <title>{{ config('app.name') }}</title>
        <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};


        </script>
        {{-- app.js => obsahuje kompilovaný vuejs , vuetify --}}
        <script src="{{ asset('js/app.js') }}" defer></script>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        {{-- css --}}
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" rel="stylesheet">
        <style> *{ text-transform: none !important; } </style>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
            }
        </style>
    </head>

    <body>
        {{-- registrace vuejs --}}
        <v-app id="app">
            <router-view></router-view>
        </v-app>
    </body>
</html>
