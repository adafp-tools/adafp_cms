@extends($layout)
@section('content')
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-10">{{ __('Leadership') }}</h1>

        @php
            $grouped = $people->groupBy(fn($p) => $p->group?->label() ?? 'Other');
        @endphp

        @foreach($grouped as $groupName => $members)
            <div class="mb-12">
                <h2 class="text-xl font-semibold mb-6 pb-2 border-b border-gray-200">{{ $groupName }}</h2>
                <ul class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 list-none p-0">
                    @foreach($members as $person)
                        @php $pt = $person->translation($locale) ?? $person->translations->first(); @endphp
                        <li class="text-center">
                            @if($person->photo)
                                <img src="{{ $person->photo->variantUrl('400w') ?? $person->photo->url() }}"
                                     alt="{{ $person->name }}"
                                     class="w-24 h-24 rounded-full object-cover mx-auto mb-3"
                                     loading="lazy">
                            @else
                                <div class="w-24 h-24 rounded-full bg-gray-100 mx-auto mb-3 flex items-center justify-center text-gray-400 text-3xl"
                                     aria-hidden="true">👤</div>
                            @endif
                            <p class="font-semibold">{{ $person->name }}</p>
                            @if($pt?->position)
                                <p class="text-sm text-gray-500">{{ $pt->position }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </section>
@endsection
