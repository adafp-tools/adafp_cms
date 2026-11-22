{{ __('You have been invited to join :app as :role.', ['app' => config('app.name'), 'role' => $invitation->role->label()]) }}

{{ __('Click the link below to create your account (expires in 7 days):') }}

{{ route('panel.invitation.accept', $invitation->token) }}

{{ __('If you did not expect this invitation, you may ignore this email.') }}
