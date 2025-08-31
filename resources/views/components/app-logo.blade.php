@php($setting = \App\Models\Setting::current())
<div class="flex aspect-square size-9 items-center justify-center rounded-md bg-white text-blue-600 border border-blue-100">
    @if($setting && $setting->logo_path)
        <img src="{{ Illuminate\Support\Facades\URL::to('storage/' . ltrim($setting->logo_path, '/')) }}" alt="Logo" class="h-6" />
    @else
        <x-app-logo-icon class="size-5 fill-current" />
    @endif
</div>
<div class="ms-1 grid flex-1 text-start text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{{ $setting->site_name ?? config('app.name', 'Facebook Insgin Admin') }}</span>
</div>
