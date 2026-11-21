@extends($layout)
@section('content')
    <section class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8">Upcoming Events</h1>

        @if($events->isEmpty())
            <p class="text-gray-500">No upcoming events.</p>
        @else
            <ul class="space-y-6 list-none p-0">
                @foreach($events as $event)
                    @php $t = $event->translation($locale) ?? $event->translations->first(); @endphp
                    <li>
                        <article class="flex flex-wrap gap-4 p-5 border border-gray-200 rounded-lg hover:shadow-sm transition-shadow">
                            <div class="text-center min-w-[64px] bg-[var(--color-primary)] text-[var(--color-text-on-primary)] rounded p-2">
                                <div class="text-2xl font-bold leading-none">{{ $event->starts_at?->format('d') }}</div>
                                <div class="text-xs uppercase mt-1">{{ $event->starts_at?->format('M Y') }}</div>
                            </div>
                            <div class="flex-1">
                                <h2 class="font-semibold text-xl">
                                    <a href="{{ route('public.events.show', ['locale' => $locale, 'slug' => $event->slug]) }}"
                                       class="hover:underline focus:ring-2 focus:ring-gray-400 focus:outline-none rounded">
                                        {{ $t?->title ?? $event->slug }}
                                    </a>
                                </h2>
                                @if($event->location)
                                    <p class="text-sm text-gray-500 mt-1">{{ $event->location }}</p>
                                @endif
                                @if($event->starts_at)
                                    <p class="text-sm text-gray-500">
                                        {{ $event->starts_at->format('H:i') }}
                                        @if($event->ends_at) – {{ $event->ends_at->format('H:i') }}@endif
                                        {{ $event->timezone ? '(' . $event->timezone . ')' : '' }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $events->links() }}
            </div>
        @endif
    </section>
@endsection
