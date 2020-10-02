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
    <link rel="icon" href="{{ URL::asset('/favicon.ico') }}" type="image/x-icon"/>
    <link rel="icon" href="{{ URL::asset('/favicon-16x16.png') }}" type="image/x-icon"/>
    <link rel="icon" href="{{ URL::asset('/favicon-32x32.png') }}" type="image/x-icon"/>

    @if (Auth::check())
        <meta name="user_id" content="{{ Auth::user()->id }}" />
    @else
        <meta name="user_id" content="" />
    @endif

    <title>{{ config('app.name') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;800&display=swap" rel="stylesheet">
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
