@extends($layout)
@section('content')
    <article class="max-w-3xl mx-auto px-4 py-12">
        <header class="mb-8">
            @if($post->featuredImage)
                <img src="{{ $post->featuredImage->variantUrl('800w') ?? $post->featuredImage->url() }}"
                     alt="{{ $translation->title ?? '' }}"
                     class="w-full h-64 object-cover rounded-lg mb-6"
                     loading="eager">
            @endif
            <p class="text-sm text-gray-500 mb-2">
                {{ $post->published_at?->format('d F Y') }}
                @if($post->author_name) &middot; {{ $post->author_name }}@endif
            </p>
            <h1 class="text-3xl font-bold leading-snug">{{ $translation->title ?? $post->slug }}</h1>
            @if($translation->summary ?? null)
                <p class="mt-3 text-lg text-gray-600">{{ $translation->summary }}</p>
            @endif
        </header>

        <div class="prose max-w-none">
            {!! $body !!}
        </div>

        <footer class="mt-10 pt-6 border-t border-gray-200">
            <a href="{{ route('public.news.index', $locale) }}"
               class="text-[var(--color-primary)] hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                {{ __('← Back to News') }}
            </a>
        </footer>
    </article>
@endsection
