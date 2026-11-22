@component('mail::message')
**{{ __('New :type form submission', ['type' => ucfirst($submission->form_type)]) }}**

**{{ __('Name:') }}** {{ $submission->name }}
**{{ __('Email:') }}** {{ $submission->email }}
@if($submission->phone)
**{{ __('Phone:') }}** {{ $submission->phone }}
@endif
@if($submission->subject)
**{{ __('Subject:') }}** {{ $submission->subject }}
@endif
@if($submission->message)
**{{ __('Message:') }}**
{{ $submission->message }}
@endif
@if($submission->party_role)
**{{ __('Role requested:') }}** {{ $submission->party_role }}
@endif
@if($submission->dob)
**{{ __('Date of birth:') }}** {{ $submission->dob->format('d M Y') }}
@endif
@if($submission->address)
**{{ __('Address:') }}** {{ $submission->address }}
@endif

**{{ __('Received:') }}** {{ $submission->created_at->toDateTimeString() }}
**{{ __('Locale:') }}** {{ $submission->locale }}
@endcomponent
