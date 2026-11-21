<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') — {{ config('cms.product_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans" x-data>

@if(config('cms.instance') === 'demo')
<div class="bg-amber-500 text-white text-center text-sm py-1 font-medium">
    Demo installation — no real data, content resets nightly
</div>
@endif

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="w-56 bg-slate-800 text-slate-200 flex flex-col shrink-0">
        <div class="px-4 py-5 border-b border-slate-700">
            <span class="text-sm font-semibold text-slate-400">{{ config('cms.product_name') }}</span>
            <div class="text-xs text-slate-500 mt-0.5">v{{ config('cms.version') }}</div>
        </div>

        <nav class="flex-1 py-4 space-y-0.5 px-2" aria-label="Admin navigation">
            <a href="{{ route('panel.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700 {{ request()->routeIs('panel.dashboard') ? 'bg-slate-700 text-white' : '' }}">
                Dashboard
            </a>

            @can('publishContent', App\Models\User::class)
            <div class="px-3 pt-3 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Content</div>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Pages</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">News &amp; Posts</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Events</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">People</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Documents</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Gallery</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Menus</a>
            @endcan

            @can('viewSubmissions', App\Models\User::class)
            <div class="px-3 pt-3 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Forms</div>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Contact submissions</a>
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Join requests</a>
            @endcan

            @can('manageUsers', App\Models\User::class)
            <div class="px-3 pt-3 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">Admin</div>
            <a href="{{ route('panel.users.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700 {{ request()->routeIs('panel.users*') ? 'bg-slate-700 text-white' : '' }}">
                Users
            </a>
            @endcan

            @can('viewHealth', App\Models\User::class)
            <a href="{{ route('panel.health') }}"
               class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700 {{ request()->routeIs('panel.health') ? 'bg-slate-700 text-white' : '' }}">
                Health
            </a>
            @endcan

            @can('manageSettings', App\Models\User::class)
            <a href="#" class="flex items-center gap-2 px-3 py-2 rounded text-sm hover:bg-slate-700">Settings</a>
            @endcan
        </nav>

        <div class="p-4 border-t border-slate-700 text-xs text-slate-400">
            {{ auth()->user()->name }}<br>
            <span class="text-slate-500">{{ auth()->user()->role->label() }}</span>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between">
            <h1 class="text-base font-semibold text-slate-800">@yield('title', 'Dashboard')</h1>
            <form method="POST" action="{{ route('panel.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-slate-500 hover:text-slate-800">Sign out</button>
            </form>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-300 text-green-800 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-3 bg-amber-50 border border-amber-300 text-amber-800 rounded text-sm">
                    {{ session('warning') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-300 text-red-800 rounded text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
