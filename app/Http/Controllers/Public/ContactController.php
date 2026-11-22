<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\FormSubmissionNotification;
use App\Models\FormSubmission;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    use ResolvesLocale;

    public function show(string $locale): View
    {
        $enabled = Setting::get('contact_form_enabled', '1') === '1';
        $theme   = $this->currentTheme();

        return view("themes.{$theme}.contact", array_merge(
            $this->themeData($locale),
            [
                'pageTitle'       => __('forms.contact_title'),
                'metaDescription' => '',
                'headerMenu'      => $this->menuItems('header'),
                'footerMenu'      => $this->menuItems('footer'),
                'hreflangLinks'   => [],
                'canonicalUrl'    => route('public.contact', $locale),
                'formEnabled'     => $enabled,
                'privacyUrl'      => Setting::get('privacy_policy_url', ''),
            ]
        ));
    }

    public function submit(string $locale, Request $r): RedirectResponse
    {
        // Rate limit: 3 per IP per 60 minutes
        $key     = 'contact_rate_' . hash('sha256', $r->ip() . config('app.key'));
        $attempts = Cache::get($key, 0);
        if ($attempts >= 3) {
            return back()->withErrors(['email' => __('forms.rate_limited')])->withInput();
        }
        Cache::put($key, $attempts + 1, now()->addMinutes(60));

        // Honeypot
        if ($r->filled('website')) {
            return back()->with('success', __('forms.success_contact'));
        }

        $r->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'consent' => ['required', 'accepted'],
        ]);

        $submission = FormSubmission::create([
            'form_type'  => 'contact',
            'locale'     => $locale,
            'name'       => $r->input('name'),
            'email'      => $r->input('email'),
            'subject'    => $r->input('subject'),
            'message'    => $r->input('message'),
            'consent'    => true,
            'consent_at' => now(),
            'ip_hash'    => hash('sha256', ($r->ip() ?? '0.0.0.0') . config('app.key')),
        ]);

        if ($notifyEmail = Setting::get('notification_email', '')) {
            Mail::to($notifyEmail)->send(new FormSubmissionNotification($submission));
        }

        return back()->with('success', __('forms.success_contact'));
    }
}
