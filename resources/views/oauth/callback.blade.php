<html>
<head>
  <meta charset="utf-8">
  <script>
    window.opener.postMessage({ token: "{{ $token }}" }, "{{ url('/') }}")
    window.close()
  </script>
</head>
<body>
</body>
</html>
