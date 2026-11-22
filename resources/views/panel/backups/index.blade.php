@extends('panel.layout')

@section('title', __('panel.backups'))

@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('panel.backups') }}</h1>
        <form method="POST" action="{{ route('panel.backups.run') }}">
            @csrf
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">
                {{ __('panel.run_backup') }}
            </button>
        </form>
    </div>

    @if(session('success'))
        <div role="alert" class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(empty($files))
        <p class="text-gray-500 text-sm">{{ __('panel.no_backups') }}</p>
    @else
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <th class="text-start px-4 py-2 border-b">{{ __('panel.file') }}</th>
                    <th class="text-start px-4 py-2 border-b">{{ __('panel.size') }}</th>
                    <th class="text-start px-4 py-2 border-b">{{ __('panel.date') }}</th>
                    <th class="text-end px-4 py-2 border-b">{{ __('panel.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($files as $file)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-xs">{{ $file['name'] }}</td>
                    <td class="px-4 py-2 text-gray-600">
                        {{ number_format($file['size'] / 1024, 1) }} KB
                    </td>
                    <td class="px-4 py-2 text-gray-600">
                        {{ date('d M Y H:i', $file['modified']) }}
                    </td>
                    <td class="px-4 py-2 text-end space-x-2">
                        <a href="{{ route('panel.backups.download', ['file' => $file['name']]) }}"
                           class="text-blue-600 hover:underline text-xs">{{ __('panel.download') }}</a>
                        <form method="POST"
                              action="{{ route('panel.backups.destroy', ['file' => $file['name']]) }}"
                              class="inline"
                              onsubmit="return confirm('{{ __('panel.delete_backup_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">{{ __('panel.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
