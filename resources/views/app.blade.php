<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIPTU.V3') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/spa.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-gray-50 text-gray-800">
    <script>
      // Sync initial dark mode with saved preference to avoid flash
      (function(){
        try {
          var isDark = localStorage.getItem('siptuv3:theme:dark') === '1';
          if (isDark) document.documentElement.classList.add('dark');
        } catch(e) {}
      })();
    </script>
    @inertia
</body>
</html>
