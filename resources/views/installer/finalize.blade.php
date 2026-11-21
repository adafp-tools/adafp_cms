@extends('installer.layout')
@section('title', 'Finalizing')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-4">Step 5 of 5 — Finalizing installation</h2>

    <ul class="space-y-3 mb-6">
        <li class="flex items-start gap-3">
            <span class="{{ $migrated ? 'text-green-600' : 'text-red-600' }} mt-0.5">{{ $migrated ? '✓' : '✗' }}</span>
            <div>
                <span class="font-medium text-slate-700">Database migrations</span>
                @unless($migrated)<p class="text-sm text-red-600">Migration failed. Check database credentials and permissions.</p>@endunless
            </div>
        </li>

        <li class="flex items-start gap-3">
            <span class="{{ $storageOk ? 'text-green-600' : 'text-amber-600' }} mt-0.5">{{ $storageOk ? '✓' : '⚠' }}</span>
            <div>
                <span class="font-medium text-slate-700">Storage symlink</span>
                @unless($storageOk)
                    <p class="text-sm text-amber-700 mt-1">
                        The <code>/storage/</code> symlink is not accessible over HTTP. This can happen on some Apache configurations.<br>
                        Uploaded files will be served through a PHP route instead — this works but is slightly slower. No action needed.
                    </p>
                @endunless
            </div>
        </li>

        <li>
            <div class="flex items-start gap-3 mb-2">
                <span class="{{ $exposurePassed ? 'text-green-600' : 'text-red-600' }} mt-0.5">{{ $exposurePassed ? '✓' : '✗' }}</span>
                <span class="font-medium text-slate-700">Sensitive file exposure test</span>
            </div>
            <ul class="ms-6 space-y-1">
                @foreach($exposureResults as $r)
                <li class="text-sm flex gap-2">
                    <span class="{{ $r['pass'] ? 'text-green-600' : 'text-red-600' }}">{{ $r['pass'] ? '✓' : '✗' }}</span>
                    <code class="text-slate-600">{{ $r['path'] }}</code>
                    <span class="text-slate-500">(HTTP {{ $r['status'] ?: 'no response' }})</span>
                </li>
                @endforeach
            </ul>
            @unless($exposurePassed)
            <div class="ms-6 mt-2 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                <strong>Action required:</strong> Some sensitive files are reachable over HTTP.
                Ensure your document root points to <code>public/</code>, or that your <code>.htaccess</code>
                rules deny access to files outside <code>public/</code>. Installation cannot complete until these pass.
                Fix your hosting configuration, then <a href="{{ route('installer.finalize') }}" class="underline">retry</a>.
            </div>
            @endunless
        </li>
    </ul>

    @if($allPassed)
        <div class="p-3 bg-green-50 border border-green-300 text-green-800 rounded mb-4">
            Installation complete. The lock file has been created.
        </div>
        <a href="{{ route('installer.complete') }}"
           class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
            View next steps →
        </a>
    @else
        <p class="text-sm text-slate-500">Fix the errors above and <a href="{{ route('installer.finalize') }}" class="underline text-blue-600">reload this page</a>.</p>
    @endif
</div>
@endsection
