@extends('panel.layout')
@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg border border-slate-200 p-4">
        <div class="text-sm text-slate-500">Version</div>
        <div class="text-lg font-semibold text-slate-800 mt-1">{{ config('cms.version') }}</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4">
        <div class="text-sm text-slate-500">Site</div>
        <div class="text-lg font-semibold text-slate-800 mt-1">{{ config('app.name') }}</div>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 p-4">
        <div class="text-sm text-slate-500">Your role</div>
        <div class="text-lg font-semibold text-slate-800 mt-1">{{ auth()->user()->role->label() }}</div>
    </div>
</div>

<div class="bg-white rounded-lg border border-slate-200">
    <div class="px-4 py-3 border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-700">Recent activity</h2>
    </div>
    <ul class="divide-y divide-slate-100">
        @forelse($recentActivity as $log)
        <li class="px-4 py-2 text-sm flex items-center gap-3">
            <span class="text-slate-400 shrink-0 w-32 text-xs">
                {{ $log->created_at->diffForHumans() }}
            </span>
            <span class="text-slate-500">{{ $log->user_name ?? '—' }}</span>
            <span class="text-slate-700">{{ $log->action }}</span>
            @if($log->target_label)
                <span class="text-slate-400 text-xs">→ {{ $log->target_label }}</span>
            @endif
        </li>
        @empty
        <li class="px-4 py-4 text-sm text-slate-400 text-center">No activity yet.</li>
        @endforelse
    </ul>
</div>
@endsection
