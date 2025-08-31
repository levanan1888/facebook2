<x-layouts.app.sidebar title="Thiết lập Website">
    <flux:main>
        @php
            $initialName = old('site_name', $setting?->site_name ?? config('app.name'));
            $initialLogoUrl = ($setting && $setting->logo_path)
                ? \Illuminate\Support\Facades\URL::to('storage/' . ltrim($setting->logo_path, '/'))
                : '';
        @endphp
        <div class="p-6" x-data='{ 
            name: @js($initialName), 
            logoPreview: @js($initialLogoUrl), 
            handleLogo(e){ const f = e.target.files[0]; if(!f) return; this.logoPreview = URL.createObjectURL(f); } 
        }'>
            @include('partials.settings-heading')

            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="mb-6 flex items-center gap-4">
                <div class="flex aspect-square h-12 w-12 items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-white">
                    <template x-if="logoPreview">
                        <img :src="logoPreview" alt="Logo preview" class="h-10" />
                    </template>
                    <template x-if="!logoPreview">
                        <span class="text-xs text-gray-400">No logo</span>
                    </template>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Tên hiển thị:</div>
                    <div class="text-base font-semibold" x-text="name"></div>
                </div>
            </div>

            <form action="{{ route('settings.website.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Tên website</label>
                    <input type="text" name="site_name" x-model="name" value="{{ old('site_name', $initialName) }}" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                    @error('site_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Logo</label>
                    <input type="file" name="logo" accept="image/*" @change="handleLogo" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Lưu</button>
                </div>
            </form>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
