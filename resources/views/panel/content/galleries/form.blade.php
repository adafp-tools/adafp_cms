@extends('panel.layout')
@section('title', isset($gallery) ? __('panel.edit_gallery') : __('panel.add_gallery'))

@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-semibold mb-6">
        {{ isset($gallery) ? __('panel.edit_gallery') : __('panel.add_gallery') }}
    </h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ isset($gallery) ? route('panel.galleries.update', $gallery) : route('panel.galleries.store') }}">
        @csrf
        @if(isset($gallery)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $gallery->slug ?? '') }}"
                       class="input w-full font-mono" placeholder="{{ __('panel.slug_auto') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.status') }}</label>
                <select name="status" class="input w-full">
                    @foreach(\App\Enums\ContentStatus::cases() as $s)
                        <option value="{{ $s->value }}"
                            {{ old('status', $gallery->status->value ?? 'draft') === $s->value ? 'selected' : '' }}>
                            {{ ucfirst($s->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Translation titles --}}
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
                    $t   = $gallery?->translation($locale);
                    $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
                @endphp
                <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.title') }}</label>
                        <input type="text" name="translations[{{ $locale }}][title]"
                               value="{{ old("translations.$locale.title", $t?->title) }}" class="input w-full">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Image picker --}}
        <div class="mt-6">
            <h3 class="text-sm font-medium mb-3">{{ __('panel.images') }}</h3>
            <div class="grid grid-cols-4 gap-3" id="gallery-images">
                @foreach(($gallery ? $gallery->images : collect()) as $img)
                    <div class="relative">
                        <input type="hidden" name="image_ids[]" value="{{ $img->media_id }}">
                        <img src="{{ $img->media->thumbnailUrl() }}" class="w-full aspect-square object-cover rounded border">
                        <button type="button" onclick="this.closest('div').remove()"
                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs leading-5 text-center">&times;</button>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="openMediaPicker()"
                    class="mt-3 text-sm text-indigo-600 hover:underline">
                + {{ __('panel.add_images') }}
            </button>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.galleries.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
