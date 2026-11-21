@extends($layout)
@section('content')

    {{-- Full-width hero --}}
    <section class="bg-[var(--color-primary)] text-[var(--color-text-on-primary)] py-24">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-5xl font-extrabold leading-tight mb-6">{{ $siteName }}</h1>
            <p class="text-xl opacity-90 mb-10">Together we build the future.</p>
            <a href="{{ route('public.page.show', ['locale' => $locale, 'slug' => 'join']) }}"
               class="inline-block px-8 py-4 bg-[var(--color-secondary)] text-white text-lg font-bold rounded-lg
                      hover:opacity-90 focus:ring-4 focus:ring-white focus:outline-none">
                Join us today
            </a>
        </div>
    </section>

    {{-- Manifesto highlights --}}
    @php
        use App\Enums\ContentStatus;
        use App\Enums\PageTemplate;
        $manifestoPages = \App\Models\Page::where('status', ContentStatus::Published)
            ->where('template', PageTemplate::Default)
            ->with('translations')
            ->latest('published_at')
            ->limit(3)
            ->get();
    @endphp

    @if($manifestoPages->count() > 0)
    <section class="max-w-4xl mx-auto px-4 py-16">
        <h2 class="text-2xl font-bold mb-6 text-center">Our Programme</h2>
        <ul class="space-y-3 list-none p-0">
            @foreach($manifestoPages as $mp)
                @php $mt = $mp->translation($locale) ?? $mp->translations->first(); @endphp
                <li class="flex items-start gap-3 p-4 border-s-4 border-[var(--color-secondary)] bg-gray-50 rounded-e">
                    <span class="text-[var(--color-secondary)] font-bold text-xl" aria-hidden="true">▶</span>
                    <a href="{{ route('public.page.show', ['locale' => $locale, 'slug' => $mp->slug]) }}"
                       class="font-semibold text-lg hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                        {{ $mt?->title ?? $mp->slug }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
    @endif

    {{-- Latest news list --}}
    @if($posts->count() > 0)
    <section class="bg-gray-50 py-14">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Latest News</h2>
            <ul class="space-y-4 list-none p-0">
                @foreach($posts->take(5) as $post)
                    @php $t = $post->translation($locale) ?? $post->translations->first(); @endphp
                    <li class="flex flex-wrap items-baseline gap-3">
                        <time class="text-sm text-gray-500 shrink-0" datetime="{{ $post->published_at?->toDateString() }}">
                            {{ $post->published_at?->format('d M Y') }}
                        </time>
                        <a href="{{ route('public.news.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
                           class="font-medium hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                            {{ $t?->title ?? $post->slug }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6 text-end">
                <a href="{{ route('public.news.index', $locale) }}"
                   class="text-[var(--color-primary)] font-medium hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                    All news &rarr;
                </a>
            </div>
        </div>
    </section>
    @endif

@endsection
