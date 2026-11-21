<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class TwoFactorController extends Controller
{
    // Setup: show QR code for first-time enrollment
    public function setup(Request $request)
    {
        $user = $request->user();
        if ($user->twoFactorEnabled()) {
            return redirect()->route('panel.dashboard');
        }
        // Enable (generates secret) so we can show the QR code
        if (! $user->two_factor_secret) {
            app(EnableTwoFactorAuthentication::class)($user);
        }
        return view('panel.auth.two-factor-setup', [
            'qrCodeSvg'     => $user->twoFactorQrCodeSvg(),
            'setupKey'      => decrypt($user->two_factor_secret),
            'recoveryCodes' => json_decode(decrypt($user->two_factor_recovery_codes), true),
        ]);
    }

    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => 'required|string|digits:6']);
        try {
            app(ConfirmTwoFactorAuthentication::class)(
                $request->user(), $request->input('code')
            );
        } catch (\Throwable) {
            return back()->withErrors(['code' => 'The code is incorrect. Please try again.']);
        }
        AuditLog::record('2fa_enabled');
        return redirect()->route('panel.dashboard')
            ->with('success', 'Two-factor authentication is now active.');
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        if ($user->requires2FA()) {
            return back()->withErrors(['2fa' => 'Your role requires two-factor authentication.']);
        }
        app(DisableTwoFactorAuthentication::class)($user);
        AuditLog::record('2fa_disabled');
        return back()->with('success', '2FA disabled.');
    }

    // Challenge: verify TOTP code on login
    public function challenge()
    {
        return view('panel.auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $user   = Auth::user();
        $code   = str_replace(' ', '', $request->input('code'));
        $engine = app(\Laravel\Fortify\Actions\ValidateTwoFactorAuthenticationCode::class);

        $valid = strlen($code) === 6 && is_numeric($code)
            ? $engine->__invoke($user, $code)
            : $this->validateRecoveryCode($user, $code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $request->session()->put('two_factor_verified', true);
        AuditLog::record('2fa_verified');
        return redirect()->intended(route('panel.dashboard'));
    }

    public function regenerateCodes(Request $request)
    {
        app(GenerateNewRecoveryCodes::class)($request->user());
        AuditLog::record('2fa_recovery_regenerated');
        return back()->with('success', 'Recovery codes regenerated.');
    }

    private function validateRecoveryCode($user, string $code): bool
    {
        $codes = json_decode(decrypt($user->two_factor_recovery_codes ?? '""'), true) ?? [];
        $index = array_search($code, $codes);
        if ($index === false) return false;
        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => encrypt(json_encode(array_values($codes)))])->save();
        return true;
    }
}
