{{-- resources/views/guest/unit-report.blade.php --}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش واحد</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @filamentStyles
    @livewireStyles
    @vite('resources/css/filament/admin/theme.css')
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-5xl mx-auto p-4">
        {{-- فرم انتخاب واحد و Livewire --}}
        @yield('content')
    </div>
    @filamentScripts
        @vite('resources/js/app.js')
</body>
</html>
