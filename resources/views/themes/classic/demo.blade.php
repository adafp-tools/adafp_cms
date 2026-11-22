@extends($layout)
@section('content')
<main class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-4">{{ __('Demo') }}</h1>
    <p class="text-gray-600 mb-8">{{ __('This is a demonstration site. Explore freely — content resets every night at 02:30.') }}</p>

    {{-- Credentials box --}}
    <section class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">{{ __('Demo login credentials:') }}</h2>
        <dl class="space-y-2 text-sm font-mono">
            <div class="flex gap-2">
                <dt class="font-sans font-medium text-blue-800 w-24">{{ __('Email:') }}</dt>
                <dd class="text-blue-900">{{ $demoEmail }}</dd>
            </div>
            <div class="flex gap-2">
                <dt class="font-sans font-medium text-blue-800 w-24">{{ __('Password:') }}</dt>
                <dd class="text-blue-900">{{ $demoPassword }}</dd>
            </div>
        </dl>
        <a href="/{{ $locale }}/{{ $panelPath }}"
           class="mt-5 inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
            {{ __('Open the admin panel') }}
        </a>
    </section>

    {{-- Allowed / Blocked --}}
    <div class="grid sm:grid-cols-2 gap-6 mb-8">
        <section>
            <h2 class="text-base font-semibold text-green-700 mb-3">{{ __('What you can do:') }}</h2>
            <ul class="space-y-1 text-sm text-gray-700 list-disc list-inside">
                <li>{{ __('Create, edit, publish or delete content (posts, pages, events, people, documents)') }}</li>
                <li>{{ __('Upload media (images, PDFs — max 2 MB)') }}</li>
                <li>{{ __('Manage menus and translations') }}</li>
            </ul>
        </section>
        <section>
            <h2 class="text-base font-semibold text-red-700 mb-3">{{ __('What you cannot do:') }}</h2>
            <ul class="space-y-1 text-sm text-gray-700 list-disc list-inside">
                <li>{{ __('Change your password, name, or email') }}</li>
                <li>{{ __('Invite other users') }}</li>
                <li>{{ __('Access settings, backups, or the updater') }}</li>
                <li>{{ __('Enable two-factor authentication') }}</li>
            </ul>
        </section>
    </div>

    <p class="text-sm text-gray-500">{{ __('Nothing you type into forms on this site is saved or sent.') }}</p>
</main>
@endsection
