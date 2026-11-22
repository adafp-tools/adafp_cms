@extends('panel.layout')
@section('title', __('panel.settings'))

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Logo upload --}}
    <div class="bg-white rounded-lg border border-slate-200 p-6 mb-6">
        <h2 class="font-semibold text-slate-700 mb-4">{{ __('panel.site_logo') }}</h2>
        @if(!empty($settings['logo_path']))
            <img src="{{ url('storage/' . $settings['logo_path']) }}" alt="Current logo" class="h-16 mb-4">
        @endif
        <form method="POST" action="{{ route('panel.settings.logo') }}" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center gap-3">
                <input type="file" name="logo" accept="image/*"
                       class="text-sm file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded text-sm hover:bg-slate-600">{{ __('panel.upload') }}</button>
            </div>
            @error('logo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </form>
    </div>

    {{-- Main settings form --}}
    <form method="POST" action="{{ route('panel.settings.update') }}" class="bg-white rounded-lg border border-slate-200 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="site_name">{{ __('panel.site_name') }}</label>
            <input id="site_name" name="site_name" type="text" value="{{ old('site_name', $settings['site_name']) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" required>
            @error('site_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="theme">{{ __('panel.theme') }}</label>
            <select id="theme" name="theme"
                    class="border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                <option value="classic" @selected(old('theme', $settings['theme']) === 'classic')>Classic</option>
            </select>
        </div>

        <div class="grid grid-cols-3 gap-4">
            @php $colorFields = ['primary_color' => __('panel.primary_colour'), 'secondary_color' => __('panel.secondary_colour'), 'text_on_primary' => __('panel.text_on_primary')]; @endphp
        @foreach($colorFields as $key => $label)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="{{ $key }}">{{ $label }}</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="{{ $key }}_picker" value="{{ old($key, $settings[$key]) }}"
                           oninput="document.getElementById('{{ $key }}').value = this.value"
                           class="h-9 w-10 rounded border border-slate-300 cursor-pointer p-0.5">
                    <input id="{{ $key }}" name="{{ $key }}" type="text"
                           value="{{ old($key, $settings[$key]) }}"
                           pattern="^#[0-9a-fA-F]{6}$"
                           class="flex-1 border border-slate-300 rounded px-2 py-1.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-slate-400">
                </div>
                @error($key) <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endforeach
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="default_locale">{{ __('panel.default_locale') }}</label>
            <select id="default_locale" name="default_locale"
                    class="border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                @foreach($supportedLocales as $lc)
                    <option value="{{ $lc }}" @selected(old('default_locale', $settings['default_locale']) === $lc)>{{ strtoupper($lc) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <fieldset>
                <legend class="block text-sm font-medium text-slate-700 mb-2">{{ __('panel.active_locales') }}</legend>
                <div class="flex flex-wrap gap-4">
                    @foreach($supportedLocales as $lc)
                        @php
                            $activeList = array_map('trim', explode(',', $settings['active_locales']));
                        @endphp
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" name="active_locales_check[]" value="{{ $lc }}"
                                   @checked(in_array($lc, $activeList))
                                   class="rounded focus:ring-2 focus:ring-slate-400">
                            {{ strtoupper($lc) }}
                        </label>
                    @endforeach
                </div>
                {{-- Hidden computed field --}}
                <input type="hidden" id="active_locales_hidden" name="active_locales" value="{{ old('active_locales', $settings['active_locales']) }}">
            </fieldset>
        </div>

        <div class="grid grid-cols-3 gap-4">
            @php $toggleFields = ['maintenance_mode' => __('panel.maintenance_mode'), 'noindex' => __('panel.noindex'), 'page_counter_enabled' => __('panel.page_counter')]; @endphp
        @foreach($toggleFields as $key => $label)
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="hidden" name="{{ $key }}" value="0">
                <input type="checkbox" name="{{ $key }}" value="1"
                       @checked((string)(old($key, $settings[$key])) === '1')
                       class="rounded focus:ring-2 focus:ring-slate-400">
                {{ $label }}
            </label>
            @endforeach
        </div>

        <hr class="border-slate-200">

        <hr class="border-slate-200">

        <details class="text-sm">
            <summary class="font-medium text-slate-700 cursor-pointer">{{ __('panel.forms_notifications') }}</summary>
            <div class="mt-3 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="hidden" name="contact_form_enabled" value="0">
                        <input type="checkbox" name="contact_form_enabled" value="1"
                               @checked((string)(old('contact_form_enabled', $settings['contact_form_enabled'])) === '1')
                               class="rounded focus:ring-2 focus:ring-slate-400">
                        {{ __('panel.enable_contact_form') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="hidden" name="join_form_enabled" value="0">
                        <input type="checkbox" name="join_form_enabled" value="1"
                               @checked((string)(old('join_form_enabled', $settings['join_form_enabled'])) === '1')
                               class="rounded focus:ring-2 focus:ring-slate-400">
                        {{ __('panel.enable_join_form') }}
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="notification_email">{{ __('panel.notification_email') }}</label>
                    <input id="notification_email" name="notification_email" type="email" max="255"
                           value="{{ old('notification_email', $settings['notification_email']) }}"
                           placeholder="{{ __('panel.notification_email_hint') }}"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    @error('notification_email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="notification_from_name">{{ __('panel.from_name') }}</label>
                        <input id="notification_from_name" name="notification_from_name" type="text" max="200"
                               value="{{ old('notification_from_name', $settings['notification_from_name']) }}"
                               class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="notification_from_email">{{ __('panel.from_email') }}</label>
                        <input id="notification_from_email" name="notification_from_email" type="email" max="255"
                               value="{{ old('notification_from_email', $settings['notification_from_email']) }}"
                               class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="privacy_policy_url">{{ __('panel.privacy_policy_url') }}</label>
                    <input id="privacy_policy_url" name="privacy_policy_url" type="text" max="500"
                           value="{{ old('privacy_policy_url', $settings['privacy_policy_url']) }}"
                           placeholder="https://... or /en/privacy"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                </div>
            </div>
        </details>

        <details class="text-sm">
            <summary class="font-medium text-slate-700 cursor-pointer">{{ __('panel.ai_translation') }}</summary>
            <div class="mt-3 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="ai_anthropic_key">{{ __('panel.anthropic_key') }}</label>
                    <input id="ai_anthropic_key" name="ai_anthropic_key" type="password" autocomplete="new-password"
                           placeholder="{{ __('panel.api_key_hint') }}"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <p class="text-xs text-slate-400 mt-1">{{ __('panel.api_key_note') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="ai_glossary">{{ __('panel.ai_glossary') }}</label>
                    <textarea id="ai_glossary" name="ai_glossary" rows="4"
                              placeholder="{{ __('panel.glossary_placeholder') }}"
                              class="w-full border border-slate-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-slate-400">{{ old('ai_glossary', $settings['ai_glossary']) }}</textarea>
                </div>
            </div>
        </details>

        {{-- CRM integration --}}
        <fieldset class="border border-slate-200 rounded-lg p-4 space-y-4">
            <legend class="text-sm font-semibold text-slate-700 px-2">{{ __('panel.crm_integration') }}</legend>

            <div class="flex items-center gap-3">
                <input type="hidden" name="crm_push_enabled" value="0">
                <input id="crm_push_enabled" name="crm_push_enabled" type="checkbox" value="1"
                       {{ old('crm_push_enabled', $settings['crm_push_enabled']) == '1' ? 'checked' : '' }}
                       class="rounded border-slate-300 text-slate-700 focus:ring-slate-400">
                <label for="crm_push_enabled" class="text-sm font-medium text-slate-700">{{ __('panel.crm_push_enabled') }}</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="crm_webhook_url">{{ __('panel.crm_webhook_url') }}</label>
                <input id="crm_webhook_url" name="crm_webhook_url" type="url" max="500"
                       value="{{ old('crm_webhook_url', $settings['crm_webhook_url']) }}"
                       placeholder="https://crm.example.com/webhooks/cms"
                       class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none">
                @error('crm_webhook_url') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="crm_webhook_secret">{{ __('panel.crm_webhook_secret') }}</label>
                <input id="crm_webhook_secret" name="crm_webhook_secret" type="password" max="500"
                       placeholder="{{ $settings['crm_webhook_secret_set'] ? '••••••••' : __('panel.enter_secret_hint') }}"
                       autocomplete="new-password"
                       class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none">
                @if($settings['crm_webhook_secret_set'])
                    <p class="text-slate-500 text-xs mt-1">{{ __('panel.secret_set') }}</p>
                @endif
            </div>

            <div class="border-t border-slate-100 pt-4 flex items-center gap-3">
                <input type="hidden" name="crm_sso_enabled" value="0">
                <input id="crm_sso_enabled" name="crm_sso_enabled" type="checkbox" value="1"
                       {{ old('crm_sso_enabled', $settings['crm_sso_enabled']) == '1' ? 'checked' : '' }}
                       class="rounded border-slate-300 text-slate-700 focus:ring-slate-400">
                <label for="crm_sso_enabled" class="text-sm font-medium text-slate-700">{{ __('panel.crm_sso_enabled') }}</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="crm_sso_secret">{{ __('panel.crm_sso_secret') }}</label>
                <input id="crm_sso_secret" name="crm_sso_secret" type="password" max="500"
                       placeholder="{{ $settings['crm_sso_secret_set'] ? '••••••••' : __('panel.enter_secret_hint') }}"
                       autocomplete="new-password"
                       class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none">
                @if($settings['crm_sso_secret_set'])
                    <p class="text-slate-500 text-xs mt-1">{{ __('panel.secret_set') }}</p>
                @endif
            </div>
        </fieldset>

        <div class="pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-slate-800 text-white rounded font-medium hover:bg-slate-700 focus:ring-2 focus:ring-slate-400 focus:outline-none">
                {{ __('panel.save_settings') }}
            </button>
        </div>
    </form>
</div>

<script>
// Sync checkboxes → hidden field for active_locales
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[name="active_locales_check[]"]');
    const hidden     = document.getElementById('active_locales_hidden');
    function update() {
        const vals = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);
        hidden.value = vals.join(',') || 'en';
    }
    checkboxes.forEach(c => c.addEventListener('change', update));
    update();
});
</script>
@endsection
