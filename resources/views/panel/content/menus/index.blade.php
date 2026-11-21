@extends('panel.layout')
@section('title', __('panel.menus'))

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold mb-6">{{ __('panel.menus') }}</h1>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3">{{ __('panel.location') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium capitalize">{{ $menu->location->value }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('panel.menus.edit', $menu) }}" class="text-indigo-600 hover:underline">{{ __('panel.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">{{ __('panel.no_menus') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
