@extends('panel.layout')
@section('title', 'Health')

@section('content')
<div class="space-y-6">

    {{-- System info --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">System</div>
        <dl class="divide-y divide-slate-100">
            @foreach([
                'CMS version'   => $cmsVersion,
                'Instance'      => $instance,
                'PHP'           => $phpVersion,
                'Database'      => $dbVersion,
                'Disk free'     => $diskFree,
                'Last backup'   => $lastBackup ?? 'Never',
                'Cron last run' => $cronLastRun ?? 'Not recorded',
                'Queue backlog' => $queueBacklog . ' jobs',
            ] as $label => $value)
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
            Sensitive file exposure
            <span class="{{ $exposurePassed ? 'text-green-600' : 'text-red-600' }} font-semibold">
                {{ $exposurePassed ? 'All pass' : 'Issues found' }}
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
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">Storage</div>
        <div class="px-4 py-3 text-sm flex items-center gap-2">
            <span class="{{ $storageWritable ? 'text-green-600' : 'text-red-600' }}">
                {{ $storageWritable ? '✓' : '✗' }}
            </span>
            <span class="text-slate-600">storage/app/ writable</span>
        </div>
    </div>

    {{-- Mail test --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">Mail</div>
        <div class="px-4 py-3">
            <form action="{{ route('panel.health.mail-test') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 text-sm font-medium">
                    Send test email to {{ auth()->user()->email }}
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
