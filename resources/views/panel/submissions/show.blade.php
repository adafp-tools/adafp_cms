@extends('panel.layout')
@section('title', __('panel.submission_detail'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('panel.submissions.index') }}"
           class="text-sm text-blue-600 hover:underline">← {{ __('panel.back_to_submissions') }}</a>
    </div>

    <div class="bg-white rounded-lg border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $submission->form_type === 'join' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                {{ ucfirst($submission->form_type) }}
            </span>
            <span class="text-xs text-slate-400">{{ $submission->created_at->format('d M Y H:i') }}</span>
        </div>

        @php $fields = [
            'name'       => __('forms.name'),
            'email'      => __('forms.email'),
            'phone'      => __('forms.phone'),
            'subject'    => __('forms.subject'),
            'message'    => __('forms.message'),
            'party_role' => __('forms.party_role'),
            'dob'        => __('forms.dob'),
            'address'    => __('forms.address'),
            'locale'     => __('panel.locale'),
            'consent_at' => __('panel.consent_at'),
        ]; @endphp

        @foreach($fields as $field => $label)
            @if(!empty($submission->$field))
            <div>
                <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $label }}</dt>
                <dd class="mt-0.5 text-sm text-slate-800 whitespace-pre-wrap">
                    @if($field === 'dob'){{ $submission->dob->format('d M Y') }}
                    @elseif($field === 'consent_at'){{ $submission->consent_at?->format('d M Y H:i') }}
                    @else{{ $submission->$field }}@endif
                </dd>
            </div>
            @endif
        @endforeach
    </div>

    <div class="mt-4 flex gap-3">
        @unless($submission->read_at)
        <form method="POST" action="{{ route('panel.submissions.read', $submission->id) }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded text-sm hover:bg-slate-600">
                {{ __('panel.mark_read') }}
            </button>
        </form>
        @endunless

        <form method="POST" action="{{ route('panel.submissions.destroy', $submission->id) }}"
              onsubmit="return confirm('{{ __('panel.delete_confirm') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                {{ __('panel.delete') }}
            </button>
        </form>
    </div>
</div>
@endsection
