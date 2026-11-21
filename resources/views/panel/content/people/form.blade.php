@extends('panel.layout')
@section('title', isset($person) ? __('panel.edit_person') : __('panel.add_person'))

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">
        {{ isset($person) ? __('panel.edit_person') : __('panel.add_person') }}
    </h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ isset($person) ? route('panel.people.update', $person) : route('panel.people.store') }}">
        @csrf
        @if(isset($person)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.name') }} *</label>
                <input type="text" name="name" required
                       value="{{ old('name', $person->name ?? '') }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.group') }}</label>
                <select name="group" class="input w-full">
                    @foreach($groups as $group)
                        <option value="{{ $group->value }}"
                            {{ old('group', $person->group->value ?? 'other') === $group->value ? 'selected' : '' }}>
                            {{ $group->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.email') }}</label>
                <input type="email" name="email" value="{{ old('email', $person->email ?? '') }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.phone') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $person->phone ?? '') }}" class="input w-full">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="show_email" value="0">
                <input type="checkbox" name="show_email" value="1" id="show_email"
                       {{ old('show_email', $person->show_email ?? false) ? 'checked' : '' }}>
                <label for="show_email" class="text-sm">{{ __('panel.show_email') }}</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="show_phone" value="0">
                <input type="checkbox" name="show_phone" value="1" id="show_phone"
                       {{ old('show_phone', $person->show_phone ?? false) ? 'checked' : '' }}>
                <label for="show_phone" class="text-sm">{{ __('panel.show_phone') }}</label>
            </div>
        </div>

        <x-panel.media-picker name="photo_id"
            :value="old('photo_id', $person->photo_id ?? null)"
            :label="__('panel.photo')" />

        <div class="mt-6" x-data="{ tab: '{{ $locales[0] }}' }">
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
                    $t   = $person?->translation($locale);
                    $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
                @endphp
                <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.position') }}</label>
                        <input type="text" name="translations[{{ $locale }}][position]"
                               value="{{ old("translations.$locale.position", $t?->position) }}" class="input w-full">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.bio') }}</label>
                        <textarea name="translations[{{ $locale }}][bio]" rows="4"
                                  class="input w-full">{{ old("translations.$locale.bio", $t?->bio) }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.people.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
