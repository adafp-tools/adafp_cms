@extends($layout)
@section('content')
    <article class="max-w-3xl mx-auto px-4 py-12">
        <header class="mb-8">
            <h1 class="text-3xl font-bold">{{ $translation->title ?? $event->slug }}</h1>
            <dl class="mt-4 text-sm text-gray-600 space-y-1">
                @if($event->starts_at)
                    <div class="flex gap-2">
                        <dt class="font-medium">{{ __('Date:') }}</dt>
                        <dd>{{ $event->starts_at->format('d F Y, H:i') }}
                            @if($event->ends_at) – {{ $event->ends_at->format('H:i') }}@endif
                            {{ $event->timezone ? '(' . $event->timezone . ')' : '' }}
                        </dd>
                    </div>
                @endif
                @if($event->location)
                    <div class="flex gap-2">
                        <dt class="font-medium">{{ __('Location:') }}</dt>
                        <dd>{{ $event->location }}</dd>
                    </div>
                @endif
                @if($event->online_url)
                    <div class="flex gap-2">
                        <dt class="font-medium">{{ __('Online:') }}</dt>
                        <dd><a href="{{ $event->online_url }}" class="text-[var(--color-primary)] underline" rel="noopener">{{ __('Join online') }}</a></dd>
                    </div>
                @endif
            </dl>
        </header>

        @if($translation->description ?? null)
            <div class="prose max-w-none">
                {!! $translation->description !!}
            </div>
        @endif

        <footer class="mt-10 pt-6 border-t border-gray-200">
            <a href="{{ route('public.events.index', $locale) }}"
               class="text-[var(--color-primary)] hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                &larr; {{ __('Back to Events') }}
            </a>
        </footer>
    </article>
@endsection
