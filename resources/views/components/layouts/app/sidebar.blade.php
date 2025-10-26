<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Dashboard')" class="grid">
                    @can('dashboard.view')
                        <flux:navlist.item icon="home" :href="route('facebook.overview')" :current="request()->routeIs('facebook.overview')" wire:navigate>{{ __('Overview (All Platforms)') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            </flux:navlist>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Facebook')" class="grid">
                    @can('facebook.overview')
                        <flux:navlist.item icon="layout-grid" :href="route('facebook.overview')" :current="request()->routeIs('facebook.overview')" wire:navigate>{{ __('Facebook Overview') }}</flux:navlist.item>
                    @endcan
                    @can('facebook.hierarchy')
                        <flux:navlist.item icon="building-office-2" :href="route('facebook.hierarchy')" :current="request()->routeIs('facebook.hierarchy')" wire:navigate>{{ __('Hierarchy') }}</flux:navlist.item>
                    @endcan
                    <!-- @can('facebook.analytics')
                        <flux:navlist.item icon="chart-bar" :href="route('facebook.analytics')" :current="request()->routeIs('facebook.analytics')" wire:navigate>{{ __('Analytics') }}</flux:navlist.item>
                    @endcan -->
                    <!-- @can('facebook.data_raw')
                        <flux:navlist.item icon="document-text" :href="route('facebook.data-raw')" :current="request()->routeIs('facebook.data-raw')" wire:navigate>{{ __('Data Raw') }}</flux:navlist.item>
                    @endcan -->
                    @can('view-facebook-data')
                        <flux:navlist.item icon="document-text" :href="route('facebook.data-management.index')" :current="request()->routeIs('facebook.data-management.*')" wire:navigate>{{ __('Data Management') }}</flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('facebook.data-management.pages')" :current="request()->routeIs('facebook.data-management.pages')" wire:navigate>{{ __('Quản lý Page') }}</flux:navlist.item>
                    @endcan
                    @can('facebook.analytics')
                        <flux:navlist.item icon="chart-bar" :href="route('facebook.messaging.report')" :current="request()->routeIs('facebook.messaging.report')" wire:navigate>{{ __('Messaging Report') }}</flux:navlist.item>
                        <flux:navlist.item icon="film" :href="route('facebook.content-insights')" :current="request()->routeIs('facebook.content-insights')" wire:navigate>{{ __('Content Insights') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            </flux:navlist>

            <!-- <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Management')" class="grid">
                    <flux:navlist.item icon="cog" href="#" :current="false">{{ __('Settings') }}</flux:navlist.item>
                    <flux:navlist.item icon="bell" href="#" :current="false">{{ __('Notifications') }}</flux:navlist.item>
                    <flux:navlist.item icon="document-text" href="#" :current="false">{{ __('Reports') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist> -->

            <!-- Admin Menu -->
            <x-admin-menu />

            <flux:spacer />

            <!-- <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist> -->

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
        @stack('scripts')
    </body>
</html>
