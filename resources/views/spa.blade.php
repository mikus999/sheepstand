@php
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'locales' => config('app.locales'),
    'googleAuth' => config('services.google.client_id'),
    'facebookAuth' => config('services.facebook.client_id'),
    'googleMaps' => config('services.google.maps_id'),
    'environment' => config('app.env')
];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{csrf_token()}}">
    
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#084389">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#084389">


    @if (Auth::check())
        <meta name="user_id" content="{{ Auth::user()->id }}" />
    @else
        <meta name="user_id" content="" />
    @endif

    <title>{{ config('app.name') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Wendy+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ mix('dist/css/app.css') }}">

    <!-- VUETIFY RESOURCES -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet"> 

    <!--DevTools-->
    <script src="http://localhost:8098"></script>

    <!--<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_id') }}"></script>-->
</head>

<body>
  <div id="app"></div>

  {{-- Global configuration object --}}
  <script>
    window.config = @json($config);
  </script>


  {{-- Load the application scripts --}}
  <script src="{{ mix('dist/js/app.js') }}"></script>
</body>
</html>
