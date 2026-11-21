@extends('panel.layout')
@section('title', isset($page) ? __('panel.edit_page') : __('panel.add_page'))

@section('content')
<div class="p-6 max-w-4xl">
    <h1 class="text-2xl font-semibold mb-6">
        {{ isset($page) ? __('panel.edit_page') : __('panel.add_page') }}
    </h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST"
          action="{{ isset($page) ? route('panel.pages.update', $page) : route('panel.pages.store') }}">
        @csrf
        @if(isset($page)) @method('PUT') @endif

        {{-- Meta fields --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug ?? '') }}"
                       class="input w-full font-mono" placeholder="{{ __('panel.slug_auto') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.template') }}</label>
                <select name="template" class="input w-full">
                    @foreach($templates as $tmpl)
                        <option value="{{ $tmpl->value }}"
                            {{ old('template', $page->template->value ?? 'default') === $tmpl->value ? 'selected' : '' }}>
                            {{ $tmpl->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.status') }}</label>
                <select name="status" class="input w-full">
                    @foreach(\App\Enums\ContentStatus::cases() as $s)
                        <option value="{{ $s->value }}"
                            {{ old('status', $page->status->value ?? 'draft') === $s->value ? 'selected' : '' }}>
                            {{ ucfirst($s->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.publish_at') }}</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', isset($page) ? $page->published_at?->format('Y-m-d\TH:i') : '') }}"
                       class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.parent_page') }}</label>
                <select name="parent_id" class="input w-full">
                    <option value="">— {{ __('panel.none') }} —</option>
                    @foreach($parents as $p)
                        @php $pt = $p->translation(config('cms.default_locale', 'en')); @endphp
                        <option value="{{ $p->id }}"
                            {{ old('parent_id', $page->parent_id ?? '') == $p->id ? 'selected' : '' }}>
                            {{ $pt?->title ?? $p->slug }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 mt-5">
                <input type="hidden" name="show_in_menu" value="0">
                <input type="checkbox" name="show_in_menu" value="1" id="show_in_menu"
                       {{ old('show_in_menu', $page->show_in_menu ?? false) ? 'checked' : '' }}>
                <label for="show_in_menu" class="text-sm">{{ __('panel.show_in_menu') }}</label>
            </div>
        </div>

        <x-panel.media-picker name="featured_image_id"
            :value="old('featured_image_id', $page->featured_image_id ?? null)"
            :label="__('panel.featured_image')" />

        {{-- Translation tabs --}}
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
                    $t   = $page?->translation($locale);
                    $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';
                @endphp
                <div x-show="tab === '{{ $locale }}'" dir="{{ $dir }}">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">{{ __('panel.title') }}</label>
                        <input type="text" name="translations[{{ $locale }}][title]"
                               value="{{ old("translations.$locale.title", $t?->title) }}"
                               class="input w-full">
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
                    {{-- AI translate button --}}
                    @if($locale !== config('cms.default_locale', 'en'))
                        <button type="button"
                                onclick="aiTranslate('{{ $locale }}', '{{ config('cms.default_locale', 'en') }}')"
                                class="text-sm text-indigo-600 hover:underline">
                            {{ __('panel.ai_translate') }}
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.pages.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
async function aiTranslate(targetLocale, sourceLocale) {
    const srcTitle = document.querySelector(`[name="translations[${sourceLocale}][title]"]`);
    const srcBody  = document.querySelector(`[name="translations[${sourceLocale}][body]"]`);
    if (!srcTitle?.value && !srcBody?.value) return;

    const res = await fetch('{{ route('panel.ai.translate') }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({
            text: (srcTitle?.value || '') + '\n\n' + (srcBody?.value || ''),
            target_locale: targetLocale,
            source_locale: sourceLocale,
        }),
    });
    const json = await res.json();
    if (json.error) { alert(json.error); return; }

    const lines = json.text.split('\n\n');
    const tgtTitle = document.querySelector(`[name="translations[${targetLocale}][title]"]`);
    if (tgtTitle) tgtTitle.value = lines[0] || '';
    const tgtBody = document.querySelector(`[name="translations[${targetLocale}][body]"]`);
    if (tgtBody) tgtBody.value = lines.slice(1).join('\n\n');
}
</script>
@endpush
@endsection
