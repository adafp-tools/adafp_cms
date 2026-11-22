@extends($layout)
@section('content')
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8">{{ $translation->title ?? $gallery->slug }}</h1>

        @if($translation->description ?? null)
            <p class="text-gray-600 mb-8">{{ $translation->description }}</p>
        @endif

        @if($gallery->images->isEmpty())
            <p class="text-gray-500">{{ __('No images in this gallery.') }}</p>
        @else
            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 list-none p-0">
                @foreach($gallery->images as $image)
                    @php
                        $it = method_exists($image, 'translation')
                            ? ($image->translation($locale) ?? $image->translations->first())
                            : null;
                    @endphp
                    <li>
                        <figure>
                            <img src="{{ url('storage/' . $image->path) }}"
                                 alt="{{ $it?->caption ?? '' }}"
                                 class="w-full h-48 object-cover rounded"
                                 loading="lazy">
                            @if($it?->caption)
                                <figcaption class="text-xs text-gray-500 mt-1 px-1">{{ $it->caption }}</figcaption>
                            @endif
                        </figure>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
