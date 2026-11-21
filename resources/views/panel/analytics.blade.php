@extends('panel.layout')
@section('title', 'Analytics')

@section('content')
<div class="max-w-3xl">
    <p class="text-sm text-slate-500 mb-6">Total page views: <strong>{{ number_format($totalViews) }}</strong></p>

    @if($topPages->isEmpty())
        <p class="text-slate-500">No data yet. Enable the page counter in <a href="{{ route('panel.settings.index') }}" class="underline">Settings</a> to start collecting views.</p>
    @else
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-start px-4 py-3 font-medium text-slate-600">Path</th>
                        <th class="text-end px-4 py-3 font-medium text-slate-600">Views</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topPages as $row)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-slate-700">{{ $row->path }}</td>
                            <td class="px-4 py-3 text-end text-slate-600">{{ number_format($row->views) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
