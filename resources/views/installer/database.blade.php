@extends('installer.layout')
@section('title', 'Database')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6" x-data="dbForm()">
    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Step :n of 5 — Database connection', ['n' => 2]) }}</h2>

    <div x-show="tested && ok" class="mb-4 p-3 bg-green-50 border border-green-300 text-green-800 rounded text-sm">
        {{ __('Connection successful.') }}
    </div>
    <div x-show="tested && !ok" class="mb-4 p-3 bg-red-50 border border-red-300 text-red-800 rounded text-sm">
        <strong>{{ __('Connection failed:') }}</strong> <span x-text="error"></span>
    </div>

    <form id="db-form" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Host') }}</label>
                <input name="db_host" type="text" value="127.0.0.1"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Port') }}</label>
                <input name="db_port" type="number" value="3306"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Database name') }}</label>
            <input name="db_database" type="text" placeholder="party_cms"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Username') }}</label>
                <input name="db_username" type="text"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Password') }}</label>
                <input name="db_password" type="password"
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="button" @click="test()"
                class="px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 text-sm font-medium"
                :disabled="testing" x-text="testing ? '{{ __('Testing…') }}' : '{{ __('Test connection') }}'">{{ __('Test connection') }}
            </button>
            <span x-show="tested && ok">
                <a href="{{ route('installer.site') }}"
                   class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium text-sm">
                    {{ __('Continue →') }}
                </a>
            </span>
        </div>
    </form>
</div>

<script>
function dbForm() {
    return {
        testing: false, tested: false, ok: false, error: '',
        async test() {
            this.testing = true; this.tested = false;
            const form = document.getElementById('db-form');
            const data = new FormData(form);
            try {
                const r = await fetch('{{ route('installer.database.test') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value,
                               'Accept': 'application/json' },
                    body: data,
                });
                const j = await r.json();
                this.ok = j.success; this.error = j.error || '';
            } catch(e) { this.ok = false; this.error = e.message; }
            this.testing = false; this.tested = true;
        }
    }
}
</script>
@endsection
