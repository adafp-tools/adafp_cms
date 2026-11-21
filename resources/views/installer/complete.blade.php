@extends('installer.layout')
@section('title', 'Installation complete')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-2">Installation complete</h2>
    <p class="text-slate-600 mb-6">Your site is installed. Complete the two steps below before going live.</p>

    <div class="space-y-6">
        <div>
            <h3 class="font-semibold text-slate-700 mb-2">1. Add a cron job</h3>
            <p class="text-sm text-slate-500 mb-2">
                Add this line to your server's crontab (cPanel → Cron Jobs or SSH <code>crontab -e</code>).
                It runs the scheduler every minute, which handles scheduled publishing and the queue.
            </p>
            <pre class="bg-slate-100 text-slate-800 text-xs p-3 rounded overflow-x-auto select-all">{{ $cronLine }}</pre>
        </div>

        <div>
            <h3 class="font-semibold text-slate-700 mb-2">2. Set up two-factor authentication</h3>
            <p class="text-sm text-slate-500 mb-2">
                Your owner account requires two-factor authentication. You will be prompted to set it up
                on first login.
            </p>
            <a href="{{ url($panelPath . '/login') }}"
               class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                Go to admin panel →
            </a>
        </div>
    </div>
</div>
@endsection
