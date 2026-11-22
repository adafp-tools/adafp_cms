@extends('panel.layout')
@section('title', __('panel.submissions'))

@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-semibold text-slate-700">
        {{ __('panel.submissions') }}
        @if($unreadCount > 0)
            <span class="ms-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                {{ $unreadCount }} {{ __('panel.unread') }}
            </span>
        @endif
    </h2>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('panel.submissions.export') }}">
            @csrf
            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
            <button type="submit" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs hover:bg-slate-200">
                {{ __('panel.export_csv') }}
            </button>
        </form>
        @if(auth()->user()->role->canExportBackup())
        <form method="POST" action="{{ route('panel.submissions.purge') }}"
              onsubmit="return confirm('{{ __('panel.purge_confirm') }}')">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 rounded text-xs hover:bg-red-100">
                {{ __('panel.purge_deleted') }}
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex gap-3 mb-4 text-sm">
    <select name="type" class="border border-slate-300 rounded px-2 py-1 text-sm" onchange="this.form.submit()">
        <option value="">{{ __('panel.all_types') }}</option>
        <option value="contact" @selected(request('type') === 'contact')>{{ __('panel.contact') }}</option>
        <option value="join" @selected(request('type') === 'join')>{{ __('panel.join') }}</option>
    </select>
    <select name="read_status" class="border border-slate-300 rounded px-2 py-1 text-sm" onchange="this.form.submit()">
        <option value="">{{ __('panel.all_statuses') }}</option>
        <option value="unread" @selected(request('read_status') === 'unread')>{{ __('panel.unread') }}</option>
        <option value="read" @selected(request('read_status') === 'read')>{{ __('panel.read') }}</option>
    </select>
</form>

<div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-600 uppercase tracking-wide">
            <tr>
                <th class="px-4 py-3 text-start">{{ __('panel.type') }}</th>
                <th class="px-4 py-3 text-start">{{ __('panel.name') }}</th>
                <th class="px-4 py-3 text-start">{{ __('panel.email') }}</th>
                <th class="px-4 py-3 text-start">{{ __('panel.locale') }}</th>
                <th class="px-4 py-3 text-start">{{ __('panel.received') }}</th>
                <th class="px-4 py-3 text-start">{{ __('panel.status') }}</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($submissions as $s)
            <tr class="{{ $s->read_at ? 'text-slate-500' : 'font-medium text-slate-800' }}">
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $s->form_type === 'join' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($s->form_type) }}
                    </span>
                </td>
                <td class="px-4 py-3">{{ $s->name }}</td>
                <td class="px-4 py-3">{{ $s->email }}</td>
                <td class="px-4 py-3 uppercase">{{ $s->locale }}</td>
                <td class="px-4 py-3">{{ $s->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-3">
                    @if($s->read_at)
                        <span class="text-xs text-slate-400">{{ __('panel.read') }}</span>
                    @else
                        <span class="text-xs text-red-600 font-semibold">{{ __('panel.unread') }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-end">
                    <a href="{{ route('panel.submissions.show', $s->id) }}"
                       class="text-xs text-blue-600 hover:underline me-3">{{ __('panel.view') }}</a>
                    <form method="POST" action="{{ route('panel.submissions.destroy', $s->id) }}" class="inline"
                          onsubmit="return confirm('{{ __('panel.delete_confirm') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">{{ __('panel.delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400 text-sm">{{ __('panel.no_submissions') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $submissions->links() }}</div>
@endsection
