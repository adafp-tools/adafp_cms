<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Accept invitation — {{ config('cms.product_name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 font-sans flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h1 class="text-lg font-semibold text-slate-800 mb-1">Create your account</h1>
        <p class="text-sm text-slate-500 mb-4">
            You've been invited as <strong>{{ $inv->role->label() }}</strong> for
            <strong>{{ config('app.name') }}</strong>.
        </p>
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif
        <form action="{{ route('panel.invitation.complete', $inv->token) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="text" value="{{ $inv->email }}" disabled
                    class="w-full border border-slate-200 rounded px-3 py-2 text-sm bg-slate-50 text-slate-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Your full name</label>
                <input name="name" type="text" value="{{ old('name') }}" required maxlength="200"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password (min 12 chars)</label>
                <input name="password" type="password" required minlength="12"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm password</label>
                <input name="password_confirmation" type="password" required minlength="12"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                Create account
            </button>
        </form>
    </div>
</div>
</body>
</html>
