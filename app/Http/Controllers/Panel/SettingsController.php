<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'theme'               => Setting::get('theme', 'classic'),
            'primary_color'       => Setting::get('primary_color', '#1e3a5f'),
            'secondary_color'     => Setting::get('secondary_color', '#c0392b'),
            'text_on_primary'     => Setting::get('text_on_primary', '#ffffff'),
            'site_name'           => Setting::get('site_name', config('cms.product_name')),
            'default_locale'      => Setting::get('default_locale', 'en'),
            'active_locales'      => Setting::get('active_locales', 'en'),
            'maintenance_mode'    => Setting::get('maintenance_mode', '0'),
            'noindex'             => Setting::get('noindex', '0'),
            'page_counter_enabled'=> Setting::get('page_counter_enabled', '0'),
            'ai_glossary'              => Setting::get('ai_glossary', ''),
            'notification_email'       => Setting::get('notification_email', ''),
            'notification_from_name'   => Setting::get('notification_from_name', config('cms.product_name')),
            'notification_from_email'  => Setting::get('notification_from_email', ''),
            'join_form_enabled'        => Setting::get('join_form_enabled', '1'),
            'contact_form_enabled'     => Setting::get('contact_form_enabled', '1'),
            'privacy_policy_url'       => Setting::get('privacy_policy_url', ''),
            'crm_push_enabled'         => Setting::get('crm_push_enabled', '0'),
            'crm_webhook_url'          => Setting::get('crm_webhook_url', ''),
            'crm_webhook_secret_set'   => ! empty(Setting::get('crm_webhook_secret', '')),
            'crm_sso_enabled'          => Setting::get('crm_sso_enabled', '0'),
            'crm_sso_secret_set'       => ! empty(Setting::get('crm_sso_secret', '')),
        ];
        $supportedLocales = config('cms.supported_locales', ['en']);
        return view('panel.settings', compact('settings', 'supportedLocales'));
    }

    public function update(Request $r): RedirectResponse
    {
        $r->validate([
            'theme'            => ['required', 'in:classic,campaign'],
            'primary_color'    => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color'  => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text_on_primary'  => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'site_name'        => ['required', 'string', 'max:200'],
            'default_locale'   => ['required', 'in:' . implode(',', config('cms.supported_locales', ['en']))],
            'active_locales'   => ['required', 'string'],
            'maintenance_mode' => ['required', 'in:0,1'],
            'noindex'          => ['required', 'in:0,1'],
            'page_counter_enabled'    => ['required', 'in:0,1'],
            'notification_email'      => ['nullable', 'email', 'max:255'],
            'notification_from_name'  => ['nullable', 'string', 'max:200'],
            'notification_from_email' => ['nullable', 'email', 'max:255'],
            'join_form_enabled'       => ['nullable', 'in:0,1'],
            'contact_form_enabled'    => ['nullable', 'in:0,1'],
            'privacy_policy_url'      => ['nullable', 'string', 'max:500'],
            'crm_push_enabled'        => ['nullable', 'in:0,1'],
            'crm_webhook_url'         => ['nullable', 'url', 'max:500'],
            'crm_webhook_secret'      => ['nullable', 'string', 'max:500'],
            'crm_sso_enabled'         => ['nullable', 'in:0,1'],
            'crm_sso_secret'          => ['nullable', 'string', 'max:500'],
        ]);

        $keys = ['theme','primary_color','secondary_color','text_on_primary',
                 'site_name','default_locale','active_locales','maintenance_mode',
                 'noindex','page_counter_enabled',
                 'notification_email','notification_from_name','notification_from_email',
                 'join_form_enabled','contact_form_enabled','privacy_policy_url',
                 'crm_push_enabled','crm_webhook_url',
                 'crm_sso_enabled'];

        $booleanKeys = ['maintenance_mode', 'noindex', 'page_counter_enabled', 'join_form_enabled', 'contact_form_enabled', 'crm_push_enabled', 'crm_sso_enabled'];
        foreach ($keys as $key) {
            $default = in_array($key, $booleanKeys, true) ? '0' : '';
            Setting::set($key, $r->input($key, $default));
        }

        // AI key — only update if provided
        if ($r->filled('ai_anthropic_key')) {
            Setting::set('ai_anthropic_key', Crypt::encryptString($r->input('ai_anthropic_key')));
        }
        if ($r->has('ai_glossary')) {
            Setting::set('ai_glossary', $r->input('ai_glossary', ''));
        }

        // CRM secrets — only update if provided (never clear an existing secret with empty)
        if ($r->filled('crm_webhook_secret')) {
            Setting::set('crm_webhook_secret', Crypt::encryptString($r->input('crm_webhook_secret')));
        }
        if ($r->filled('crm_sso_secret')) {
            Setting::set('crm_sso_secret', Crypt::encryptString($r->input('crm_sso_secret')));
        }

        // WCAG AA contrast check
        $warnings = [];
        $primary   = $r->input('primary_color');
        $secondary = $r->input('secondary_color');
        $onPrimary = $r->input('text_on_primary');

        if ($this->contrastRatio($primary, $onPrimary) < 4.5) {
            $warnings[] = 'Primary colour vs text-on-primary does not meet WCAG AA (4.5:1) contrast requirement.';
        }
        if ($this->contrastRatio($secondary, $onPrimary) < 4.5) {
            $warnings[] = 'Secondary colour vs text-on-primary does not meet WCAG AA (4.5:1) contrast requirement.';
        }

        $message = 'Settings saved.';
        if ($warnings) {
            $message .= ' Warning: ' . implode(' ', $warnings);
        }

        return redirect()->route('panel.settings.index')->with('success', $message);
    }

    public function logo(Request $r): RedirectResponse
    {
        $r->validate(['logo' => ['required', 'image', 'max:5120']]);

        $media = app(MediaService::class)->store($r->file('logo'), auth()->id());
        Setting::set('logo_path', $media->directory . '/' . $media->filename);

        return redirect()->route('panel.settings.index')->with('success', 'Logo updated.');
    }

    private function hexToLinear(string $hex): float
    {
        $val = hexdec(ltrim($hex, '#'));
        $r   = (($val >> 16) & 0xff) / 255;
        $g   = (($val >> 8) & 0xff) / 255;
        $b   = ($val & 0xff) / 255;
        $linearize = fn(float $c): float =>
            $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        return 0.2126 * $linearize($r) + 0.7152 * $linearize($g) + 0.0722 * $linearize($b);
    }

    private function contrastRatio(string $hex1, string $hex2): float
    {
        $l1 = $this->hexToLinear($hex1);
        $l2 = $this->hexToLinear($hex2);
        [$light, $dark] = $l1 > $l2 ? [$l1, $l2] : [$l2, $l1];
        return ($light + 0.05) / ($dark + 0.05);
    }
}
