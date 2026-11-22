@extends('installer.layout')
@section('title', 'Requirements check')

@section('content')
@php $allOk = collect($checks)->reject(fn($c) => $c['warning'] ?? false)->every(fn($c) => $c['pass']); @endphp

<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Step :n of 5 — Server requirements', ['n' => 1]) }}</h2>

    <ul class="space-y-2 mb-6">
        @foreach($checks as $check)
        <li class="flex items-start gap-3">
            <span class="mt-0.5 {{ $check['pass'] ? 'text-green-600' : (($check['warning'] ?? false) ? 'text-amber-600' : 'text-red-600') }}">
                {{ $check['pass'] ? '✓' : (($check['warning'] ?? false) ? '⚠' : '✗') }}
            </span>
            <div>
                <span class="font-medium text-slate-700">{{ $check['label'] }}</span>
                <span class="text-slate-500 text-sm ms-2">({{ $check['found'] }})</span>
            </div>
        </li>
        @endforeach
    </ul>

    @if(! $allOk)
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded mb-4 text-sm">
            {{ __('One or more required checks failed. Contact your hosting provider to enable the missing PHP extensions or fix directory permissions before continuing.') }}
        </div>
    @endif

    @if($allOk)
        <a href="{{ route('installer.database') }}"
           class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
            {{ __('Continue →') }}
        </a>
    @else
        <button disabled
                class="inline-block px-5 py-2 bg-slate-300 text-slate-500 rounded cursor-not-allowed font-medium">
            {{ __('Continue →') }}
        </button>
        <p class="text-sm text-slate-500 mt-2">{{ __('Fix the issues above, then reload this page.') }}</p>
    @endif
</div>
@endsection
