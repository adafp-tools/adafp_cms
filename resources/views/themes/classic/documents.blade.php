@extends($layout)
@section('content')
    <section class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8">{{ __('Documents') }}</h1>

        @if($documents->isEmpty())
            <p class="text-gray-500">{{ __('No documents published yet.') }}</p>
        @else
            <ul class="space-y-4 list-none p-0">
                @foreach($documents as $doc)
                    @php $dt = $doc->translation($locale) ?? $doc->translations->first(); @endphp
                    <li class="flex flex-wrap items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="text-3xl" aria-hidden="true">📄</div>
                        <div class="flex-1">
                            <h2 class="font-semibold">{{ $dt?->title ?? pathinfo($doc->file, PATHINFO_FILENAME) }}</h2>
                            @if($dt?->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $dt->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $doc->category?->label() }}
                                @if($doc->file_size)
                                    &middot; {{ number_format($doc->file_size / 1024, 0) }} KB
                                @endif
                            </p>
                        </div>
                        <a href="{{ url('storage/' . $doc->file) }}"
                           class="shrink-0 px-4 py-2 bg-[var(--color-primary)] text-[var(--color-text-on-primary)] rounded text-sm
                                  font-medium hover:opacity-90 focus:ring-2 focus:ring-gray-400 focus:outline-none"
                           download
                           aria-label="{{ __('Download') . ' ' . ($dt?->title ?? $doc->file) }}">
                            {{ __('Download') }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
@endsection
