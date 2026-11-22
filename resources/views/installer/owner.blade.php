@extends('installer.layout')
@section('title', __('Owner account'))

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-1">{{ __('Step :n of 5 — Owner account', ['n' => 4]) }}</h2>
    <p class="text-sm text-slate-500 mb-4">
        {{ __('This is the first administrator. Two-factor authentication will be required after first login.') }}
    </p>

    <form action="{{ route('installer.owner.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Full name') }} *</label>
            <input name="name" type="text" value="{{ old('name') }}" required maxlength="200"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email address') }} *</label>
            <input name="email" type="email" value="{{ old('email') }}" required
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Password') }} *</label>
            <input name="password" type="password" required minlength="12"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-slate-500 mt-1">{{ __('Minimum 12 characters.') }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Confirm password') }} *</label>
            <input name="password_confirmation" type="password" required minlength="12"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                {{ __('Create account and finalize →') }}
            </button>
        </div>
    </form>
</div>
@endsection
