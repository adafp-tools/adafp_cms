<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('panel.sign_in') }} — {{ config('cms.product_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-6">
        <h1 class="text-xl font-serif font-bold text-slate-800">{{ config('app.name') }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ __('panel.admin_panel') }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        @if(session('status'))
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-800 rounded text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        @if(config('cms.instance') === 'demo')
        <div class="mb-5 p-4 bg-amber-50 border border-amber-300 rounded text-sm">
            <p class="font-semibold text-amber-800 mb-2">{{ __('panel.demo_credentials_title') }}</p>
            <p class="text-amber-900 font-mono text-xs mb-1">{{ config('cms.demo_login_email') }}</p>
            <p class="text-amber-900 font-mono text-xs mb-3">{{ config('cms.demo_login_password') }}</p>
            <button type="button"
                    onclick="document.getElementById('email').value='{{ config('cms.demo_login_email') }}';document.getElementById('password').value='{{ config('cms.demo_login_password') }}';"
                    class="px-3 py-1 bg-amber-200 text-amber-900 rounded text-xs font-medium hover:bg-amber-300 focus:ring-2 focus:ring-amber-400 focus:outline-none">
                {{ __('panel.demo_credentials_fill') }}
            </button>
        </div>
        @endif
        <form action="{{ route('panel.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="email">{{ __('panel.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    autocomplete="email" autofocus required
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="text-sm font-medium text-slate-700" for="password">{{ __('panel.password') }}</label>
                    <a href="{{ route('panel.password.request') }}" class="text-xs text-blue-600 hover:underline">
                        {{ __('panel.forgot_password') }}
                    </a>
                </div>
                <input id="password" name="password" type="password"
                    autocomplete="current-password" required
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                {{ __('panel.sign_in') }}
            </button>
        </form>
    </div>
</div>
</body>
</html>
