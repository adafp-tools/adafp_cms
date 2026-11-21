@extends('panel.layout')
@section('title', __('panel.posts'))

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">{{ __('panel.posts') }}</h1>
        <a href="{{ route('panel.posts.create') }}" class="btn-primary">+ {{ __('panel.add_post') }}</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">{{ __('panel.title') }}</th>
                    <th class="text-left px-4 py-3">{{ __('panel.type') }}</th>
                    <th class="text-left px-4 py-3">{{ __('panel.status') }}</th>
                    <th class="text-left px-4 py-3">{{ __('panel.published_at') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    @php $t = $post->translation(config('cms.default_locale', 'en')); @endphp
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $t?->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $post->type->value }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $post->status->value === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $post->status->value }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $post->published_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('panel.posts.edit', $post) }}" class="text-indigo-600 hover:underline">{{ __('panel.edit') }}</a>
                            <form method="POST" action="{{ route('panel.posts.destroy', $post) }}" class="inline"
                                  onsubmit="return confirm('{{ __('panel.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline">{{ __('panel.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('panel.no_posts') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection
