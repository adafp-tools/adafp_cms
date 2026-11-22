<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('Installation')) — {{ config('cms.product_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <header class="mb-8 text-center">
            <h1 class="text-2xl font-serif font-bold text-slate-800">
                {{ config('cms.product_name') }} — {{ __('Installation') }}
            </h1>
            {{-- Installer UI language selector --}}
            <div class="mt-3 flex justify-center gap-2 text-sm" aria-label="{{ __('Language') }}">
                @foreach(config('cms.supported_locales', ['en','fr','pt','ar']) as $lang)
                    @php $active = app()->getLocale() === $lang; @endphp
                    <a href="{{ route('installer.lang', $lang) }}"
                       class="px-2 py-0.5 rounded {{ $active ? 'bg-blue-600 text-white font-semibold' : 'text-slate-500 hover:text-slate-800' }}"
                       @if($active) aria-current="true" @endif
                    >{{ strtoupper($lang) }}</a>
                @endforeach
            </div>
        </header>

        {{-- Step indicator --}}
        <nav class="flex justify-center gap-2 mb-8 text-sm" aria-label="Installation steps">
            @foreach([
                ['requirements', __('Requirements')],
                ['database',     __('Database')],
                ['site',         __('Site details')],
                ['owner',        __('Owner account')],
                ['finalize',     __('Finalize')],
            ] as [$step, $label])
                @php $active = request()->routeIs("installer.{$step}*"); @endphp
                <span class="px-3 py-1 rounded-full {{ $active ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                    {{ $label }}
                </span>
            @endforeach
        </nav>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-300 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-300 text-red-800 rounded">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <main>@yield('content')</main>
    </div>
</body>
</html>
