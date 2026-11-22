@extends($layout)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16">

    <h1 class="text-3xl font-serif font-bold mb-4">{{ __('demo.heading') }}</h1>
    <p class="text-gray-600 mb-10 leading-relaxed">{{ __('demo.intro') }}</p>

    {{-- Credentials card --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8"
         x-data="{ show: false }">
        <h2 class="font-semibold text-gray-800 mb-4">{{ __('demo.credentials_heading') }}</h2>

        <dl class="space-y-3 text-sm">
            <div class="flex flex-wrap gap-2 items-center">
                <dt class="font-medium text-gray-600 w-32">{{ __('demo.email_label') }}</dt>
                <dd class="font-mono text-gray-900 select-all">{{ $demoEmail }}</dd>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <dt class="font-medium text-gray-600 w-32">{{ __('demo.password_label') }}</dt>
                <dd class="flex items-center gap-2">
                    <span class="font-mono text-gray-900 select-all"
                          x-show="show">{{ $demoPassword }}</span>
                    <span class="font-mono text-gray-400 tracking-widest"
                          x-show="!show" aria-hidden="true">••••••••</span>
                    <button type="button"
                            @click="show = !show"
                            class="text-xs text-blue-600 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                            :aria-label="show ? '{{ __('demo.hide') }}' : '{{ __('demo.show') }}'">
                        <span x-text="show ? '{{ __('demo.hide') }}' : '{{ __('demo.show') }}'"></span>
                    </button>
                </dd>
            </div>
        </dl>
    </div>

    <a href="{{ $panelUrl }}"
       class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        {{ __('demo.login_button') }} →
    </a>

    <p class="mt-8 text-sm text-gray-500">{{ __('demo.reset_note') }}</p>

</div>
@endsection
