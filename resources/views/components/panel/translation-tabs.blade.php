{{-- Translation tab bar + panels. Usage:
     <x-panel.translation-tabs :locales="$locales" :model="$page ?? null" /> --}}
@props(['locales', 'model' => null])

@php
    $localeLabels = ['en' => 'English', 'ar' => 'العربية', 'fr' => 'Français'];
@endphp

<div x-data="{ tab: '{{ $locales[0] }}' }">
    {{-- Tab bar --}}
    <div class="flex gap-1 border-b border-gray-200 mb-4">
        @foreach($locales as $locale)
            @php $label = $localeLabels[$locale] ?? strtoupper($locale); @endphp
            <button type="button"
                    @click="tab = '{{ $locale }}'"
                    :class="tab === '{{ $locale }}'
                        ? 'border-b-2 border-indigo-600 text-indigo-600'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium -mb-px">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab panels --}}
    @foreach($locales as $locale)
        @php
            $translation = $model?->translation($locale);
            $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
        @endphp
        <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
            {{ ${'slot_' . $locale} ?? $slot }}
        </div>
    @endforeach
</div>
