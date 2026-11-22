@extends($layout)
@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8">{{ __('forms.join_title') }}</h1>

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
        <form method="POST" action="{{ route('public.join.submit', $locale) }}" novalidate class="space-y-5">
            @csrf
            {{-- Honeypot --}}
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.phone') }}</label>
                    <input id="phone" name="phone" type="tel" maxlength="50"
                           value="{{ old('phone') }}"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('phone') border-red-400 @enderror">
                    @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="dob" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.dob') }}</label>
                    <input id="dob" name="dob" type="date"
                           value="{{ old('dob') }}"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('dob') border-red-400 @enderror">
                    @error('dob') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="party_role" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.party_role') }} *</label>
                <select id="party_role" name="party_role" required
                        class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('party_role') border-red-400 @enderror">
                    <option value="">—</option>
                    <option value="Member"    @selected(old('party_role') === 'Member')>{{ __('forms.role_member') }}</option>
                    <option value="Volunteer" @selected(old('party_role') === 'Volunteer')>{{ __('forms.role_volunteer') }}</option>
                    <option value="Candidate" @selected(old('party_role') === 'Candidate')>{{ __('forms.role_candidate') }}</option>
                </select>
                @error('party_role') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-slate-700 mb-1">{{ __('forms.address') }}</label>
                <textarea id="address" name="address" maxlength="1000" rows="3"
                          class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                @error('address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
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
                    {{ __('forms.submit_join') }}
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
