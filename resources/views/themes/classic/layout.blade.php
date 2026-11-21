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
        Demo site — not real party data
    </div>
@endif

<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:start-2 bg-white px-4 py-2 rounded shadow z-50">
    Skip to content
</a>

<header class="bg-[var(--color-primary)] text-[var(--color-text-on-primary)]">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('public.home', $locale) }}"
           class="font-bold text-xl focus:ring-2 focus:ring-[var(--color-text-on-primary)] focus:outline-none rounded">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-10 w-auto" loading="lazy">
            @else
                {{ $siteName }}
            @endif
        </a>

        @if(count($activeLocales ?? []) > 1)
            <nav aria-label="Language" class="flex gap-2 text-sm">
                @foreach($activeLocales as $lang)
                    <a href="{{ route('public.home', $lang) }}"
                       class="uppercase px-1 focus:ring-2 focus:ring-[var(--color-text-on-primary)] focus:outline-none rounded
                              {{ $lang === $locale ? 'underline font-semibold' : 'opacity-75 hover:opacity-100' }}"
                       lang="{{ $lang }}"
                       hreflang="{{ $lang }}"
                       aria-current="{{ $lang === $locale ? 'page' : 'false' }}">{{ strtoupper($lang) }}</a>
                @endforeach
            </nav>
        @endif

        @if(!empty($headerMenu))
            <nav aria-label="Main menu">
                <ul class="flex flex-wrap gap-4 text-sm font-medium list-none p-0 m-0">
                    @foreach($headerMenu as $item)
                        <li>
                            <a href="{{ $item['url'] ?? '#' }}"
                               class="hover:underline focus:ring-2 focus:ring-[var(--color-text-on-primary)] focus:outline-none rounded">
                                {{ $item['label'][$locale] ?? $item['label']['en'] ?? '' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </div>
</header>

<main id="main-content" tabindex="-1">
    @yield('content')
</main>

<footer class="bg-gray-900 text-gray-300 mt-16 py-8 print:hidden">
    <div class="max-w-6xl mx-auto px-4 text-sm text-center">
        <p>{{ $siteName }} &middot; {{ $currentYear }}</p>
        @if(!empty($footerMenu))
            <nav aria-label="Footer menu" class="mt-3">
                <ul class="flex flex-wrap justify-center gap-4 list-none p-0 m-0">
                    @foreach($footerMenu as $item)
                        <li>
                            <a href="{{ $item['url'] ?? '#' }}"
                               class="hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                                {{ $item['label'][$locale] ?? $item['label']['en'] ?? '' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </div>
</footer>

</body>
</html>
