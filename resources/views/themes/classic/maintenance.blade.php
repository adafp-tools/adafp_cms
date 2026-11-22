<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}" dir="{{ ($locale ?? 'en') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $siteName ?? 'Site' }} — Under Maintenance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center font-sans">
    <div class="text-center max-w-md px-6">
        <div class="text-6xl mb-6" aria-hidden="true">🔧</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Under Maintenance') }}</h1>
        <p class="text-gray-600">{{ __('This site is temporarily unavailable while we make improvements. Please check back soon.') }}</p>
    </div>
</body>
</html>
