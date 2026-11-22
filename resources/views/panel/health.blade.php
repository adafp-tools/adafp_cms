@extends('panel.layout')
@section('title', __('panel.health'))

@section('content')
<div class="space-y-6">

    {{-- System info --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">{{ __('panel.system') }}</div>
        <dl class="divide-y divide-slate-100">
            @php
            $sysInfo = [
                __('panel.cms_version')   => $cmsVersion,
                __('panel.instance')      => $instance,
                __('panel.php')           => $phpVersion,
                __('panel.database')      => $dbVersion,
                __('panel.disk_free')     => $diskFree,
                __('panel.last_backup')   => $lastBackup ?? __('panel.never'),
                __('panel.cron_last_run') => $cronLastRun ?? __('panel.not_recorded'),
                __('panel.queue_backlog') => $queueBacklog . ' ' . __('panel.jobs'),
            ];
            @endphp
            @foreach($sysInfo as $label => $value)
            <div class="px-4 py-2 flex justify-between text-sm">
                <dt class="text-slate-500">{{ $label }}</dt>
                <dd class="text-slate-800 font-medium">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </div>

    {{-- Exposure test --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700 flex justify-between">
            {{ __('panel.sensitive_exposure') }}
            <span class="{{ $exposurePassed ? 'text-green-600' : 'text-red-600' }} font-semibold">
                {{ $exposurePassed ? __('panel.all_pass') : __('panel.issues_found') }}
            </span>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach($exposureResults as $r)
            <li class="px-4 py-2 flex items-center gap-3 text-sm">
                <span class="{{ $r['pass'] ? 'text-green-600' : 'text-red-600' }}">{{ $r['pass'] ? '✓' : '✗' }}</span>
                <code class="text-slate-600">{{ $r['path'] }}</code>
                <span class="text-slate-400 text-xs ms-auto">HTTP {{ $r['status'] ?: 'no response' }}</span>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Storage --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">{{ __('panel.storage') }}</div>
        <div class="px-4 py-3 text-sm flex items-center gap-2">
            <span class="{{ $storageWritable ? 'text-green-600' : 'text-red-600' }}">
                {{ $storageWritable ? '✓' : '✗' }}
            </span>
            <span class="text-slate-600">{{ __('panel.storage_writable') }}</span>
        </div>
    </div>

    {{-- Mail test --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">{{ __('panel.mail') }}</div>
        <div class="px-4 py-3">
            <form action="{{ route('panel.health.mail-test') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 text-sm font-medium">
                    {{ __('panel.send_test_email', ['email' => auth()->user()->email]) }}
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
