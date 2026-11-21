<?php

namespace App\Http\Controllers\Panel;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function index()
    {
        $this->authorize('manageUsers', User::class);
        $users       = User::orderBy('name')->get();
        $invitations = UserInvitation::where('accepted_at', null)
            ->where('expires_at', '>', now())
            ->with('inviter')
            ->get();
        return view('panel.users.index', compact('users', 'invitations'));
    }

    public function invite(Request $request)
    {
        $this->authorize('manageUsers', User::class);
        $data = $request->validate([
            'email' => 'required|email|unique:users,email|unique:user_invitations,email',
            'role'  => 'required|in:admin,editor,translator',
        ]);

        // Owners may not invite other owners
        $role = UserRole::from($data['role']);
        $inv  = UserInvitation::generate($data['email'], $role, $request->user()->id);

        try {
            Mail::send('emails.invitation', ['invitation' => $inv], function ($m) use ($inv) {
                $m->to($inv->email)
                  ->subject('You have been invited to ' . config('app.name'));
            });
        } catch (\Throwable $e) {
            Log::error('Invitation mail failed: ' . $e->getMessage());
        }

        AuditLog::record('user_invited', 'invitation', $inv->id, $inv->email);
        return back()->with('success', 'Invitation sent to ' . $inv->email);
    }

    public function acceptInvitation(Request $request, string $token)
    {
        $inv = UserInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('panel.auth.accept-invitation', compact('inv'));
    }

    public function completeInvitation(Request $request, string $token)
    {
        $inv = UserInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $data = $request->validate([
            'name'                  => 'required|string|max:200',
            'password'              => 'required|string|min:12|confirmed',
        ]);

        $user = (new \App\Actions\Fortify\CreateNewUser)->create([
            'name'                  => $data['name'],
            'email'                 => $inv->email,
            'password'              => $data['password'],
            'password_confirmation' => $data['password'],
            'role'                  => $inv->role->value,
            'invited_by'            => $inv->invited_by,
        ]);

        $inv->update(['accepted_at' => now()]);
        AuditLog::record('user_joined', 'user', $user->id, $user->email);

        return redirect()->route('panel.login')
            ->with('success', 'Account created. You can now log in.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('manageUsers', User::class);

        // Cannot delete yourself or an owner (unless you are the owner)
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        if ($user->isOwner() && ! $request->user()->isOwner()) {
            return back()->withErrors(['user' => 'Only an owner can remove another owner.']);
        }

        AuditLog::record('user_deleted', 'user', $user->id, $user->email);
        $user->delete();
        return back()->with('success', 'User removed.');
    }
}
