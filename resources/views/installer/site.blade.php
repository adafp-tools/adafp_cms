@extends('installer.layout')
@section('title', __('Site details'))

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Step :n of 5 — Site details', ['n' => 3]) }}</h2>

    <form action="{{ route('installer.site.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Organisation name') }} *</label>
            <input name="org_name" type="text" value="{{ old('org_name') }}" required maxlength="200"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Default language') }} *</label>
                <select name="default_locale" class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="en" {{ old('default_locale','en')==='en'?'selected':'' }}>English</option>
                    <option value="fr" {{ old('default_locale')==='fr'?'selected':'' }}>Français</option>
                    <option value="pt" {{ old('default_locale')==='pt'?'selected':'' }}>Português</option>
                    <option value="ar" {{ old('default_locale')==='ar'?'selected':'' }}>العربية</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Active languages') }} *</label>
                <div class="space-y-1 mt-1">
                    @foreach(['en'=>'English','fr'=>'Français','pt'=>'Português','ar'=>'العربية'] as $code=>$name)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="active_locales[]" value="{{ $code }}"
                            {{ in_array($code, old('active_locales',['en'])) ? 'checked' : '' }}>
                        {{ $name }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Site URL') }} *</label>
            <input name="site_url" type="url" value="{{ old('site_url', $defaultUrl) }}" required
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-slate-500 mt-1">{{ __('Must match the domain this site is accessed on. Used for the exposure test.') }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Timezone') }} *</label>
                <select name="timezone" class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach(timezone_identifiers_list() as $tz)
                    <option value="{{ $tz }}" {{ old('timezone','Africa/Khartoum')===$tz?'selected':'' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Contact email') }} *</label>
                <input name="contact_email" type="email" value="{{ old('contact_email') }}" required
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                {{ __('Continue →') }}
            </button>
        </div>
    </form>
</div>
@endsection
