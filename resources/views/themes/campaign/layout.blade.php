<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($pageTitle ?? '') ? $pageTitle . ' — ' . $siteName : $siteName }}</title>
    <meta name="description" content="{{ $metaDescription ?? '' }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? '' }}">
    @foreach($hreflangLinks ?? [] as $lang => $url)
        <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
    @endforeach
    @if($noindex ?? false)
        <meta name="robots" content="noindex,nofollow">
    @endif
    <meta property="og:title" content="{{ $pageTitle ?? $siteName }}">
    <meta property="og:description" content="{{ $metaDescription ?? '' }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <style>
        :root {
            --color-primary: {{ $primaryColor ?? '#1e3a5f' }};
            --color-secondary: {{ $secondaryColor ?? '#c0392b' }};
            --color-text-on-primary: {{ $textOnPrimary ?? '#ffffff' }};
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased font-sans">

@if(config('cms.instance') === 'demo')
    <div class="bg-yellow-400 text-center text-sm py-1 font-medium text-gray-900">
        {{ __('Demo site — not real party data') }}
    </div>
@endif

<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:start-2 bg-white px-4 py-2 rounded shadow z-50">
    {{ __('Skip to content') }}
</a>

<header class="border-b border-gray-100 bg-white">
    <div class="max-w-7xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('public.home', $locale) }}"
           class="text-2xl font-extrabold text-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)] focus:outline-none rounded">
            {{ $siteName }}
        </a>

        @if(!empty($headerMenu))
            <nav aria-label="{{ __('Main menu') }}">
                <ul class="flex flex-wrap gap-6 text-sm font-semibold list-none p-0 m-0">
                    @foreach($headerMenu as $item)
                        <li>
                            <a href="{{ $item['url'] ?? '#' }}"
                               class="text-gray-700 hover:text-[var(--color-primary)] focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                                {{ $item['label'][$locale] ?? $item['label']['en'] ?? '' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <div class="flex items-center gap-4">
            @if(count($activeLocales ?? []) > 1)
                <nav aria-label="{{ __('Language') }}" class="flex gap-2 text-xs">
                    @foreach($activeLocales as $lang)
                        <a href="{{ route('public.home', $lang) }}"
                           class="uppercase px-1 py-0.5 rounded focus:ring-2 focus:ring-gray-400 focus:outline-none
                                  {{ $lang === $locale ? 'bg-[var(--color-primary)] text-[var(--color-text-on-primary)]' : 'text-gray-500 hover:text-gray-900' }}"
                           lang="{{ $lang }}"
                           hreflang="{{ $lang }}"
                           aria-current="{{ $lang === $locale ? 'page' : 'false' }}">{{ strtoupper($lang) }}</a>
                    @endforeach
                </nav>
            @endif
            <a href="{{ route('public.page.show', ['locale' => $locale, 'slug' => 'join']) }}"
               class="px-4 py-2 bg-[var(--color-secondary)] text-white rounded font-semibold text-sm
                      hover:opacity-90 focus:ring-2 focus:ring-gray-400 focus:outline-none">
                {{ __('Join us') }}
            </a>
        </div>
    </div>
</header>

<main id="main-content" tabindex="-1">
    @yield('content')
</main>

<footer class="bg-[var(--color-primary)] text-[var(--color-text-on-primary)] mt-16 py-10 print:hidden">
    <div class="max-w-7xl mx-auto px-4 text-sm">
        <p class="font-semibold text-lg mb-2">{{ $siteName }}</p>
        <p class="opacity-75">&copy; {{ $currentYear }}</p>
        @if(!empty($footerMenu))
            <nav aria-label="{{ __('Footer menu') }}" class="mt-4">
                <ul class="flex flex-wrap gap-4 list-none p-0 m-0">
                    @foreach($footerMenu as $item)
                        <li>
                            <a href="{{ $item['url'] ?? '#' }}"
                               class="opacity-75 hover:opacity-100 hover:underline focus:ring-2 focus:ring-[var(--color-text-on-primary)] focus:outline-none rounded">
                                {{ $item['label'][$locale] ?? $item['label']['en'] ?? '' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif
        @if(config('cms.instance') === 'demo')
            <p class="mt-4 text-sm opacity-75">
                <a href="/{{ $locale }}/demo"
                   class="underline hover:no-underline focus:ring-2 focus:ring-[var(--color-text-on-primary)] focus:outline-none rounded">
                    {{ __('Try the admin panel (demo)') }}
                </a>
            </p>
        @endif
    </div>
</footer>

</body>
</html>
