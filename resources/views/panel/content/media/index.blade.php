@extends('panel.layout')
@section('title', __('panel.media_library'))

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">{{ __('panel.media_library') }}</h1>
        <label class="btn-primary cursor-pointer">
            {{ __('panel.upload') }}
            <form id="upload-form" method="POST"
                  action="{{ route('panel.media.store') }}"
                  enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" name="file" accept="image/*,application/pdf"
                       onchange="document.getElementById('upload-form').submit()">
            </form>
        </label>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-2 text-sm">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($items as $item)
            <div class="group relative rounded border bg-white overflow-hidden shadow-sm">
                @if($item->isImage())
                    <img src="{{ $item->thumbnailUrl() }}" alt="{{ $item->original_filename }}"
                         class="w-full aspect-square object-cover">
                @else
                    <div class="w-full aspect-square flex items-center justify-center bg-gray-100 text-xs text-gray-500 p-2 text-center">
                        {{ strtoupper($item->extension) }}<br>{{ $item->humanFileSize() ?? '' }}
                    </div>
                @endif

                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-end transition">
                    <form method="POST" action="{{ route('panel.media.destroy', $item) }}"
                          class="w-full"
                          onsubmit="return confirm('{{ __('panel.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="w-full text-white text-xs py-1 bg-red-600 hover:bg-red-700">
                            {{ __('panel.delete') }}
                        </button>
                    </form>
                </div>

                <div class="p-1 text-xs text-gray-500 truncate" title="{{ $item->original_filename }}">
                    {{ $item->original_filename }}
                </div>
            </div>
        @empty
            <p class="col-span-6 text-gray-500">{{ __('panel.no_media') }}</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
</div>
@endsection
