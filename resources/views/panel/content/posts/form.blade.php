@extends('panel.layout')
@section('title', isset($post) ? __('panel.edit_post') : __('panel.add_post'))

@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-semibold mb-6">
        {{ isset($post) ? __('panel.edit_post') : __('panel.add_post') }}
    </h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ isset($post) ? route('panel.posts.update', $post) : route('panel.posts.store') }}">
        @csrf
        @if(isset($post)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
                       class="input w-full font-mono" placeholder="{{ __('panel.slug_auto') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.type') }}</label>
                <select name="type" class="input w-full">
                    @foreach($types as $type)
                        <option value="{{ $type->value }}"
                            {{ old('type', $post->type->value ?? 'news') === $type->value ? 'selected' : '' }}>
                            {{ ucfirst($type->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.status') }}</label>
                <select name="status" class="input w-full">
                    @foreach(\App\Enums\ContentStatus::cases() as $s)
                        <option value="{{ $s->value }}"
                            {{ old('status', $post->status->value ?? 'draft') === $s->value ? 'selected' : '' }}>
                            {{ ucfirst($s->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.publish_at') }}</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', isset($post) ? $post->published_at?->format('Y-m-d\TH:i') : '') }}"
                       class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.author_name') }}</label>
                <input type="text" name="author_name" value="{{ old('author_name', $post->author_name ?? '') }}"
                       class="input w-full">
            </div>
        </div>

        <x-panel.media-picker name="featured_image_id"
            :value="old('featured_image_id', $post->featured_image_id ?? null)"
            :label="__('panel.featured_image')" />

        <div class="mt-6" x-data="{ tab: '{{ $locales[0] }}' }">
            <div class="flex gap-1 border-b border-gray-200 mb-4">
                @foreach($locales as $locale)
                    @php $labels = ['en'=>'English','ar'=>'العربية','fr'=>'Français']; @endphp
                    <button type="button" @click="tab = '{{ $locale }}'"
                            :class="tab === '{{ $locale }}' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 text-sm font-medium -mb-px">
                        {{ $labels[$locale] ?? strtoupper($locale) }}
                    </button>
                @endforeach
            </div>

            @foreach($locales as $locale)
                @php
                    $t   = $post?->translation($locale);
                    $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
                @endphp
                <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.title') }}</label>
                        <input type="text" name="translations[{{ $locale }}][title]"
                               value="{{ old("translations.$locale.title", $t?->title) }}" class="input w-full">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.summary') }}</label>
                        <textarea name="translations[{{ $locale }}][summary]" rows="2"
                                  class="input w-full">{{ old("translations.$locale.summary", $t?->summary) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.body') }}</label>
                        <input id="trix-{{ $locale }}" type="hidden"
                               name="translations[{{ $locale }}][body]"
                               value="{{ old("translations.$locale.body", $t?->body) }}">
                        <trix-editor input="trix-{{ $locale }}" class="trix-content min-h-[200px]"></trix-editor>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.meta_description') }}</label>
                        <input type="text" name="translations[{{ $locale }}][meta_description]"
                               value="{{ old("translations.$locale.meta_description", $t?->meta_description) }}"
                               maxlength="160" class="input w-full">
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.posts.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
