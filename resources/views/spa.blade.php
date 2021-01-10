@php
@endphp
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{csrf_token()}}">

  <title>SheepStand</title>
  <meta name="title" constent="SheepStand">
  <meta name="description" content="Public Witnessing scheduler for congregations, special metropolitan witnessing programs, and service groups.">

  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#084389">
  <meta name="msapplication-TileColor" content="#2b5797">
  <meta name="theme-color" content="#084389">


  <link rel="stylesheet" href="{{ mix('dist/css/app.css') }}">
</head>

<body>
  <div id="app"></div>

  {{-- Load the application scripts --}}
  <script src="{{ mix('dist/js/app.js') }}"></script>

  <style>
    html, body {
      height: 100%;
    }
  </style>
</body>
</html>

