<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Gradient Background -->
    <div class="fixed inset-0 z-0 bg-gradient-to-br from-blue-50 via-white to-blue-100"></div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 flex items-center justify-center rounded-3xl bg-gradient-to-r from-blue-600 to-blue-700 shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105">
                    <i class="fab fa-facebook text-white text-4xl"></i>
                </div>
                <h2 class="mt-8 text-4xl font-bold text-gray-900">
                    Tạo tài khoản mới
                </h2>
                <p class="mt-3 text-lg text-gray-600 font-medium">
                    Tham gia cùng chúng tôi và bắt đầu hành trình
                </p>
            </div>

            <!-- Register Form -->
            <div class="bg-white/80 backdrop-blur-sm py-8 px-8 shadow-2xl rounded-3xl border border-white/20">
                <!-- Session Status -->
                <x-auth-session-status class="mb-6 text-center" :status="session('status')" />

                <form wire:submit="register" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Họ và tên
                        </label>
                        <input
                            wire:model="name"
                            id="name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Nhập họ và tên của bạn"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        />
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Địa chỉ email
                        </label>
                        <input
                            wire:model="email"
                            id="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="Nhập email của bạn"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mật khẩu
                        </label>
                        <input
                            wire:model="password"
                            id="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Tạo mật khẩu mới"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Xác nhận mật khẩu
                        </label>
                        <input
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Nhập lại mật khẩu"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        />
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-base font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </span>
                            Tạo tài khoản
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Hoặc đăng ký với</span>
                        </div>
                    </div>
                </div>

                <!-- Social Register Buttons -->
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="ml-2">Google</span>
                    </button>

                    <button type="button" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fab fa-facebook text-blue-600 text-lg"></i>
                        <span class="ml-2">Facebook</span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Đã có tài khoản? 
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                            Đăng nhập ngay
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
