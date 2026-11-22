@extends($layout)
@section('content')

    {{-- Hero --}}
    @php $hero = $posts->first(); @endphp
    <section class="bg-[var(--color-primary)] text-[var(--color-text-on-primary)] py-16">
        <div class="max-w-6xl mx-auto px-4">
            @if($hero)
                @php $ht = $hero->translation($locale) ?? $hero->translations->first(); @endphp
                <p class="text-sm uppercase tracking-wider opacity-75 mb-2">{{ $ht?->type?->value ?? __('Latest') }}</p>
                <h1 class="text-4xl font-bold mb-4">{{ $ht?->title ?? $siteName }}</h1>
                @if($ht?->summary)
                    <p class="text-lg opacity-90 max-w-2xl">{{ $ht->summary }}</p>
                @endif
                <a href="{{ route('public.news.show', ['locale' => $locale, 'slug' => $hero->slug]) }}"
                   class="inline-block mt-6 px-5 py-2 bg-[var(--color-secondary)] text-white rounded font-medium
                          hover:opacity-90 focus:ring-2 focus:ring-white focus:outline-none">
                    {{ __('Read more') }}
                </a>
            @else
                <h1 class="text-4xl font-bold">{{ $siteName }}</h1>
                <p class="text-lg opacity-90 mt-2">{{ __('Welcome to our website.') }}</p>
            @endif
        </div>
    </section>

    {{-- Latest news --}}
    @if($posts->count() > 0)
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-6">{{ __('Latest News') }}</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($posts as $post)
                @php $t = $post->translation($locale) ?? $post->translations->first(); @endphp
                <article class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                    @if($post->featuredImage)
                        <img src="{{ $post->featuredImage->variantUrl('400w') ?? $post->featuredImage->url() }}"
                             alt="{{ $t?->title ?? '' }}"
                             class="w-full h-48 object-cover"
                             loading="lazy">
                    @endif
                    <div class="p-4">
                        <p class="text-xs text-gray-500 mb-1">
                            {{ $post->published_at?->format('d M Y') }}
                        </p>
                        <h3 class="font-semibold text-lg mb-2">
                            <a href="{{ route('public.news.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
                               class="hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                                {{ $t?->title ?? $post->slug }}
                            </a>
                        </h3>
                        @if($t?->summary)
                            <p class="text-sm text-gray-600 line-clamp-3">{{ $t->summary }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6 text-end">
            <a href="{{ route('public.news.index', $locale) }}"
               class="text-[var(--color-primary)] font-medium hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                {{ __('All news') }} &rarr;
            </a>
        </div>
    </section>
    @endif

    {{-- Upcoming events --}}
    @if($events->count() > 0)
    <section class="bg-gray-50 py-10">
        <div class="max-w-6xl mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">{{ __('Upcoming Events') }}</h2>
            <ul class="space-y-4 list-none p-0">
                @foreach($events as $event)
                    @php $et = $event->translation($locale) ?? $event->translations->first(); @endphp
                    <li class="flex flex-wrap items-start gap-4 p-4 bg-white rounded border border-gray-200">
                        <div class="text-center min-w-[56px] bg-[var(--color-primary)] text-[var(--color-text-on-primary)] rounded p-2">
                            <div class="text-xl font-bold leading-none">{{ $event->starts_at?->format('d') }}</div>
                            <div class="text-xs uppercase">{{ $event->starts_at?->format('M Y') }}</div>
                        </div>
                        <div>
                            <h3 class="font-semibold">
                                <a href="{{ route('public.events.show', ['locale' => $locale, 'slug' => $event->slug]) }}"
                                   class="hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                                    {{ $et?->title ?? $event->slug }}
                                </a>
                            </h3>
                            @if($event->location)
                                <p class="text-sm text-gray-500">{{ $event->location }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif

    {{-- Leadership strip --}}
    @if($people->count() > 0)
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-6">{{ __('Leadership') }}</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach($people as $person)
                @php $pt = $person->translation($locale) ?? $person->translations->first(); @endphp
                <div class="text-center">
                    @if($person->photo)
                        <img src="{{ $person->photo->variantUrl('400w') ?? $person->photo->url() }}"
                             alt="{{ $person->name }}"
                             class="w-20 h-20 rounded-full object-cover mx-auto mb-2"
                             loading="lazy">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gray-200 mx-auto mb-2 flex items-center justify-center text-gray-400 text-2xl"
                             aria-hidden="true">👤</div>
                    @endif
                    <p class="font-medium text-sm">{{ $person->name }}</p>
                    @if($pt?->position)
                        <p class="text-xs text-gray-500">{{ $pt->position }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-6 text-end">
            <a href="{{ route('public.leadership', $locale) }}"
               class="text-[var(--color-primary)] font-medium hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                {{ __('All leadership') }} &rarr;
            </a>
        </div>
    </section>
    @endif

@endsection
