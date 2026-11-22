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

class JoinController extends Controller
{
    use ResolvesLocale;

    public function show(string $locale): View
    {
        $enabled = Setting::get('join_form_enabled', '1') === '1';
        $theme   = $this->currentTheme();

        return view("themes.{$theme}.join", array_merge(
            $this->themeData($locale),
            [
                'pageTitle'       => __('forms.join_title'),
                'metaDescription' => '',
                'headerMenu'      => $this->menuItems('header'),
                'footerMenu'      => $this->menuItems('footer'),
                'hreflangLinks'   => [],
                'canonicalUrl'    => route('public.join', $locale),
                'formEnabled'     => $enabled,
                'privacyUrl'      => Setting::get('privacy_policy_url', ''),
            ]
        ));
    }

    public function submit(string $locale, Request $r): RedirectResponse
    {
        // Rate limit: 3 per IP per 60 minutes
        $key      = 'join_rate_' . hash('sha256', $r->ip() . config('app.key'));
        $attempts = Cache::get($key, 0);
        if ($attempts >= 3) {
            return back()->withErrors(['email' => __('forms.rate_limited')])->withInput();
        }
        Cache::put($key, $attempts + 1, now()->addMinutes(60));

        // Honeypot
        if ($r->filled('website')) {
            return back()->with('success', __('forms.success_join'));
        }

        $r->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'dob'        => ['nullable', 'date', 'before:today'],
            'address'    => ['nullable', 'string', 'max:1000'],
            'party_role' => ['required', 'in:Member,Volunteer,Candidate'],
            'consent'    => ['required', 'accepted'],
        ]);

        $submission = FormSubmission::create([
            'form_type'  => 'join',
            'locale'     => $locale,
            'name'       => $r->input('name'),
            'email'      => $r->input('email'),
            'phone'      => $r->input('phone'),
            'dob'        => $r->input('dob'),
            'address'    => $r->input('address'),
            'party_role' => $r->input('party_role'),
            'consent'    => true,
            'consent_at' => now(),
            'ip_hash'    => hash('sha256', ($r->ip() ?? '0.0.0.0') . config('app.key')),
        ]);

        if ($notifyEmail = Setting::get('notification_email', '')) {
            Mail::to($notifyEmail)->send(new FormSubmissionNotification($submission));
        }

        if (Setting::get('crm_push_enabled', '0') === '1') {
            \App\Jobs\PushSubmissionToCrmJob::dispatch($submission);
        }

        return back()->with('success', __('forms.success_join'));
    }
}
