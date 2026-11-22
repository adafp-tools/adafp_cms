@extends($layout)
@section('content')
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8">{{ __('News') }}</h1>

        @if($posts->isEmpty())
            <p class="text-gray-500">{{ __('No posts published yet.') }}</p>
        @else
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
                            <p class="text-xs text-gray-500 mb-1">{{ $post->published_at?->format('d M Y') }}</p>
                            <h2 class="font-semibold text-lg mb-2">
                                <a href="{{ route('public.news.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
                                   class="hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                                    {{ $t?->title ?? $post->slug }}
                                </a>
                            </h2>
                            @if($t?->summary)
                                <p class="text-sm text-gray-600 line-clamp-3">{{ $t->summary }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
@endsection
