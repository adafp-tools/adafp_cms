<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('panel.two_factor_setup_heading') }} — {{ config('cms.product_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h1 class="text-lg font-semibold text-slate-800 mb-1">{{ __('panel.two_factor_setup_heading') }}</h1>
        <p class="text-sm text-slate-500 mb-5">{{ __('panel.two_factor_setup_desc') }}</p>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        <div class="flex justify-center mb-4">
            {!! $qrCodeSvg !!}
        </div>

        <details class="mb-4 text-sm text-slate-600">
            <summary class="cursor-pointer text-blue-600 hover:underline">{{ __('panel.cant_scan') }}</summary>
            <code class="block mt-2 bg-slate-100 p-2 rounded text-xs tracking-widest select-all">{{ $setupKey }}</code>
        </details>

        <form action="{{ route('panel.two-factor.setup.confirm') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="code">{{ __('panel.verification_code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" pattern="\d{6}"
                    autocomplete="one-time-code" required maxlength="6" placeholder="000000"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm text-center tracking-widest focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                {{ __('panel.confirm_activate') }}
            </button>
        </form>

        <div class="mt-6 p-3 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800">
            <strong>{{ __('panel.save_recovery_codes') }}</strong> {{ __('panel.recovery_codes_desc') }}
            <ul class="mt-2 font-mono text-xs columns-2 space-y-1">
                @foreach($recoveryCodes as $code)
                    <li class="select-all">{{ $code }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
</body>
</html>
