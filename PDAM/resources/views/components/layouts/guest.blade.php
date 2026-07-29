<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login — Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM">
    <title>{{ $title ?? 'Login' }} — Tirta Flow</title>
    <link rel="icon" href="/images/TIRTAFLOWBGPUTIH.png" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-200 font-sans antialiased" style="font-family: 'Inter', system-ui, sans-serif;">
    {{ $slot }}
    @livewireScripts
</body>
</html>
