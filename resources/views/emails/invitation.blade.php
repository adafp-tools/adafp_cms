You have been invited to join {{ config('app.name') }} as {{ $invitation->role->label() }}.

Click the link below to create your account (expires in 7 days):

{{ route('panel.invitation.accept', $invitation->token) }}

If you did not expect this invitation, you may ignore this email.
