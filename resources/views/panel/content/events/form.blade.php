@extends('panel.layout')
@section('title', isset($event) ? __('panel.edit_event') : __('panel.add_event'))

@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-semibold mb-6">
        {{ isset($event) ? __('panel.edit_event') : __('panel.add_event') }}
    </h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ isset($event) ? route('panel.events.update', $event) : route('panel.events.store') }}">
        @csrf
        @if(isset($event)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $event->slug ?? '') }}"
                       class="input w-full font-mono" placeholder="{{ __('panel.slug_auto') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.status') }}</label>
                <select name="status" class="input w-full">
                    @foreach(\App\Enums\ContentStatus::cases() as $s)
                        <option value="{{ $s->value }}"
                            {{ old('status', $event->status->value ?? 'draft') === $s->value ? 'selected' : '' }}>
                            {{ ucfirst($s->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.starts_at') }}</label>
                <input type="datetime-local" name="starts_at" required
                       value="{{ old('starts_at', isset($event) ? $event->starts_at->format('Y-m-d\TH:i') : '') }}"
                       class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.ends_at') }}</label>
                <input type="datetime-local" name="ends_at"
                       value="{{ old('ends_at', isset($event) ? $event->ends_at?->format('Y-m-d\TH:i') : '') }}"
                       class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.location') }}</label>
                <input type="text" name="location" value="{{ old('location', $event->location ?? '') }}"
                       class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.online_url') }}</label>
                <input type="url" name="online_url" value="{{ old('online_url', $event->online_url ?? '') }}"
                       class="input w-full">
            </div>
        </div>

        <div class="mt-4" x-data="{ tab: '{{ $locales[0] }}' }">
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
                    $t   = $event?->translation($locale);
                    $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
                @endphp
                <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.title') }}</label>
                        <input type="text" name="translations[{{ $locale }}][title]"
                               value="{{ old("translations.$locale.title", $t?->title) }}" class="input w-full">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.description') }}</label>
                        <textarea name="translations[{{ $locale }}][description]" rows="4"
                                  class="input w-full">{{ old("translations.$locale.description", $t?->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.location_label') }}</label>
                        <input type="text" name="translations[{{ $locale }}][location_label]"
                               value="{{ old("translations.$locale.location_label", $t?->location_label) }}"
                               class="input w-full">
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.events.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
