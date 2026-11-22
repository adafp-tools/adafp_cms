@extends($layout)
@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8">{{ __('forms.contact_title') }}</h1>

    @if(session('success'))
        <div role="alert" class="mb-6 p-4 bg-green-50 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if(!$formEnabled)
        <p class="text-slate-600">{{ __('forms.form_disabled') }}</p>
    @elseif(!session('success'))
        <form method="POST" action="{{ route('public.contact.submit', $locale) }}" novalidate class="space-y-5">
            @csrf
            {{-- Honeypot --}}
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.name') }} *</label>
                <input id="name" name="name" type="text" required maxlength="255"
                       value="{{ old('name') }}"
                       class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('name') border-red-400 @enderror">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.email') }} *</label>
                <input id="email" name="email" type="email" required maxlength="255"
                       value="{{ old('email') }}"
                       class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('email') border-red-400 @enderror">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.subject') }} *</label>
                <input id="subject" name="subject" type="text" required maxlength="255"
                       value="{{ old('subject') }}"
                       class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('subject') border-red-400 @enderror">
                @error('subject') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.message') }} *</label>
                <textarea id="message" name="message" required maxlength="2000" rows="5"
                          class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                @error('message') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="flex items-start gap-2 text-sm cursor-pointer">
                    <input id="consent" name="consent" type="checkbox" value="1"
                           @checked(old('consent'))
                           required
                           class="mt-0.5 rounded focus:ring-2 focus:ring-slate-400 @error('consent') outline outline-red-400 @enderror">
                    <span>
                        {{ __('forms.consent_label') }}
                        @if(!empty($privacyUrl))
                            — <a href="{{ $privacyUrl }}" target="_blank" class="underline hover:no-underline">{{ __('forms.privacy_policy') }}</a>
                        @endif
                    </span>
                </label>
                @error('consent') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit"
                        class="px-6 py-2.5 bg-[var(--color-primary)] text-[var(--color-text-on-primary)] rounded font-medium hover:opacity-90 focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)] focus:outline-none">
                    {{ __('forms.submit_contact') }}
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
