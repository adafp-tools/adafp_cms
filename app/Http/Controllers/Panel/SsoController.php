<?php

namespace App\Http\Controllers\Panel;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    public function callback(Request $r): RedirectResponse
    {
        if (Setting::get('crm_sso_enabled', '0') !== '1') {
            abort(403, 'SSO not enabled');
        }

        $token = $r->query('token');
        $sig   = $r->query('sig');

        if (! $token || ! $sig) {
            abort(400, 'Missing token or signature');
        }

        try {
            $rawSecret = Setting::get('crm_sso_secret', '');
            $secret    = $rawSecret ? Crypt::decryptString($rawSecret) : '';
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            abort(403, 'SSO misconfigured');
        }

        if (empty($secret)) {
            abort(403, 'SSO not configured');
        }

        $expected = hash_hmac('sha256', $token, $secret);
        if (! hash_equals($expected, $sig)) {
            abort(403, 'Invalid signature');
        }

        $data = json_decode(base64_decode($token), true);

        if (! is_array($data) || empty($data['email']) || ! isset($data['exp'])) {
            abort(400, 'Malformed token');
        }

        if ($data['exp'] < time()) {
            abort(403, 'Token expired');
        }

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'] ?? $data['email'],
                'password' => bcrypt(Str::random(32)),
                'role'     => UserRole::Editor,
            ]
        );

        Auth::login($user, false);

        return redirect()->route('panel.dashboard');
    }
}
