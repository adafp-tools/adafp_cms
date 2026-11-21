@extends('panel.layout')
@section('title', __('panel.edit_menu'))

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-2">{{ __('panel.edit_menu') }}</h1>
    <p class="text-gray-500 mb-6 capitalize">{{ $menu->location->value }}</p>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('panel.menus.update', $menu) }}">
        @csrf @method('PUT')

        <div x-data="{ tab: '{{ $locales[0] }}' }">
            <div class="flex gap-1 border-b border-gray-200 mb-4">
                @foreach($locales as $locale)
                    @php $labels = ['en'=>'English','ar'=>'العربية','fr'=>'Français']; @endphp
                    <button type="button" @click="tab = '{{ $locale }}'"
                            :class="tab === '{{ $locale }}' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500'"
                            class="px-4 py-2 text-sm font-medium -mb-px">
                        {{ $labels[$locale] ?? strtoupper($locale) }}
                    </button>
                @endforeach
            </div>

            @foreach($locales as $locale)
                @php
                    $t    = $menu->translation($locale);
                    $dir  = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
                    $json = $t ? json_encode($t->items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '[]';
                @endphp
                <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
                    <label class="block text-sm font-medium mb-1">
                        {{ __('panel.menu_items_json') }}
                    </label>
                    <textarea name="translations[{{ $locale }}][items]" rows="12"
                              class="input w-full font-mono text-xs">{{ old("translations.$locale.items", $json) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('panel.menu_items_hint') }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.menus.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
