{{-- Inline media picker. Usage:
     <x-panel.media-picker name="featured_image_id" :value="$page->featured_image_id ?? null" /> --}}
@props(['name', 'value' => null, 'label' => null])

@php
    $media = $value ? \App\Models\Media::find($value) : null;
@endphp

<div x-data="{
    mediaId: {{ $value ?? 'null' }},
    mediaUrl: '{{ $media?->thumbnailUrl() }}',
    open: false,
}" class="space-y-2">
    <input type="hidden" name="{{ $name }}" :value="mediaId">

    @if($label)
        <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif

    <div class="flex items-center gap-3">
        <div class="w-24 h-24 rounded border border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center">
            <template x-if="mediaUrl">
                <img :src="mediaUrl" class="object-cover w-full h-full" alt="">
            </template>
            <template x-if="!mediaUrl">
                <span class="text-xs text-gray-400">{{ __('panel.no_image') }}</span>
            </template>
        </div>

        <div class="flex flex-col gap-2">
            <button type="button" @click="open = true"
                    class="text-sm text-indigo-600 hover:underline">
                {{ __('panel.choose_media') }}
            </button>
            <template x-if="mediaId">
                <button type="button" @click="mediaId = null; mediaUrl = null"
                        class="text-sm text-red-500 hover:underline">
                    {{ __('panel.remove') }}
                </button>
            </template>
        </div>
    </div>

    {{-- Modal (simple inline picker) --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[80vh] overflow-y-auto p-6"
             @click.outside="open = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">{{ __('panel.select_media') }}</h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <div class="grid grid-cols-4 gap-3">
                @foreach(\App\Models\Media::where('mime_type', 'like', 'image/%')->latest()->take(60)->get() as $m)
                    <button type="button"
                            @click="mediaId = {{ $m->id }}; mediaUrl = '{{ $m->thumbnailUrl() }}'; open = false"
                            class="rounded border-2 overflow-hidden hover:border-indigo-500 transition"
                            :class="mediaId === {{ $m->id }} ? 'border-indigo-600' : 'border-transparent'">
                        <img src="{{ $m->thumbnailUrl() }}" alt="{{ $m->original_filename }}"
                             class="w-full aspect-square object-cover">
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
