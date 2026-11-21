@extends('panel.layout')
@section('title', __('panel.galleries'))

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">{{ __('panel.galleries') }}</h1>
        <a href="{{ route('panel.galleries.create') }}" class="btn-primary">+ {{ __('panel.add_gallery') }}</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">{{ __('panel.title') }}</th>
                    <th class="text-left px-4 py-3">{{ __('panel.slug') }}</th>
                    <th class="text-left px-4 py-3">{{ __('panel.status') }}</th>
                    <th class="text-left px-4 py-3">{{ __('panel.images') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($galleries as $gallery)
                    @php $t = $gallery->translation(config('cms.default_locale', 'en')); @endphp
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $t?->title ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-gray-500">{{ $gallery->slug }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $gallery->status->value === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $gallery->status->value }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $gallery->images_count ?? $gallery->images->count() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('panel.galleries.edit', $gallery) }}" class="text-indigo-600 hover:underline">{{ __('panel.edit') }}</a>
                            <form method="POST" action="{{ route('panel.galleries.destroy', $gallery) }}" class="inline"
                                  onsubmit="return confirm('{{ __('panel.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline">{{ __('panel.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('panel.no_galleries') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $galleries->links() }}</div>
</div>
@endsection
