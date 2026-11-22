@component('mail::message')
**New {{ ucfirst($submission->form_type) }} form submission**

**Name:** {{ $submission->name }}
**Email:** {{ $submission->email }}
@if($submission->phone)
**Phone:** {{ $submission->phone }}
@endif
@if($submission->subject)
**Subject:** {{ $submission->subject }}
@endif
@if($submission->message)
**Message:**
{{ $submission->message }}
@endif
@if($submission->party_role)
**Role requested:** {{ $submission->party_role }}
@endif
@if($submission->dob)
**Date of birth:** {{ $submission->dob->format('d M Y') }}
@endif
@if($submission->address)
**Address:** {{ $submission->address }}
@endif

**Received:** {{ $submission->created_at->toDateTimeString() }}
**Locale:** {{ $submission->locale }}
@endcomponent
