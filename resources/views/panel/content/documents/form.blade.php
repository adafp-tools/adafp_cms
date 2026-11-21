@extends('panel.layout')
@section('title', isset($document) ? __('panel.edit_document') : __('panel.add_document'))

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-2xl font-semibold mb-6">
        {{ isset($document) ? __('panel.edit_document') : __('panel.add_document') }}
    </h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ isset($document) ? route('panel.documents.update', $document) : route('panel.documents.store') }}">
        @csrf
        @if(isset($document)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4 mb-6">
            @if(!isset($document))
                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ __('panel.file') }} *</label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                           class="block w-full text-sm text-gray-500">
                </div>
            @else
                <div class="col-span-2 text-sm text-gray-500">
                    {{ __('panel.current_file') }}: {{ basename($document->file) }} ({{ $document->humanFileSize() }})
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.category') }}</label>
                <select name="category" class="input w-full">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->value }}"
                            {{ old('category', $document->category->value ?? 'other') === $cat->value ? 'selected' : '' }}>
                            {{ $cat->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('panel.publish_at') }}</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', isset($document) ? $document->published_at?->format('Y-m-d\TH:i') : '') }}"
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
                    $t   = $document?->translation($locale);
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
                        <textarea name="translations[{{ $locale }}][description]" rows="3"
                                  class="input w-full">{{ old("translations.$locale.description", $t?->description) }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-primary">{{ __('panel.save') }}</button>
            <a href="{{ route('panel.documents.index') }}" class="btn-secondary">{{ __('panel.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
