<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('panel.reset_password_heading') }} — {{ config('cms.product_name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 font-sans flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h1 class="text-lg font-semibold text-slate-800 mb-1">{{ __('panel.reset_password_heading') }}</h1>
        <p class="text-sm text-slate-500 mb-4">{{ __('panel.reset_password_desc') }}</p>
        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-300 text-green-800 rounded text-sm">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif
        <form action="{{ route('panel.password.email') }}" method="POST" class="space-y-4">
            @csrf
            <input name="email" type="email" value="{{ old('email') }}" required placeholder="you@example.com"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                {{ __('panel.send_reset_link') }}
            </button>
        </form>
        <p class="text-center mt-4"><a href="{{ route('panel.login') }}" class="text-sm text-blue-600 hover:underline">{{ __('panel.back_to_sign_in') }}</a></p>
    </div>
</div>
</body>
</html>
