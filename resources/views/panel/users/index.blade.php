@extends('panel.layout')
@section('title', 'Users')

@section('content')
<div class="space-y-6">

    {{-- Current users --}}
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">Team members</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-2 text-start">Name</th>
                    <th class="px-4 py-2 text-start">Email</th>
                    <th class="px-4 py-2 text-start">Role</th>
                    <th class="px-4 py-2 text-start">2FA</th>
                    <th class="px-4 py-2 text-start">Last active</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr>
                    <td class="px-4 py-2 font-medium text-slate-800">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span class="text-xs text-slate-400 ms-1">(you)</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-slate-600">{{ $user->email }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-0.5 rounded-full text-xs
                            {{ $user->isOwner() ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $user->role->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        @if($user->twoFactorEnabled())
                            <span class="text-green-600 text-xs font-medium">On</span>
                        @elseif($user->requires2FA())
                            <span class="text-red-500 text-xs font-medium">Required — not set</span>
                        @else
                            <span class="text-slate-400 text-xs">Off</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-slate-500 text-xs">
                        {{ $user->last_active_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="px-4 py-2 text-end">
                        @can('delete', $user)
                        <form action="{{ route('panel.users.destroy', $user) }}" method="POST"
                            onsubmit="return confirm('Remove {{ $user->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pending invitations --}}
    @if($invitations->isNotEmpty())
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-slate-700">Pending invitations</div>
        <ul class="divide-y divide-slate-100">
            @foreach($invitations as $inv)
            <li class="px-4 py-2 flex items-center gap-4 text-sm">
                <span class="text-slate-700">{{ $inv->email }}</span>
                <span class="text-xs text-slate-500">{{ $inv->role->label() }}</span>
                <span class="text-xs text-slate-400 ms-auto">expires {{ $inv->expires_at->diffForHumans() }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Invite form --}}
    @can('manageUsers', App\Models\User::class)
    <div class="bg-white rounded-lg border border-slate-200 p-4">
        <h3 class="font-semibold text-sm text-slate-700 mb-3">Invite a new team member</h3>
        <form action="{{ route('panel.users.invite') }}" method="POST" class="flex gap-3 flex-wrap items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                <input name="email" type="email" required value="{{ old('email') }}"
                    class="border border-slate-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Role</label>
                <select name="role"
                    class="border border-slate-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="admin">Admin</option>
                    <option value="editor" selected>Editor</option>
                    <option value="translator">Translator</option>
                </select>
            </div>
            <button type="submit"
                class="px-4 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Send invitation
            </button>
        </form>
    </div>
    @endcan

</div>
@endsection
