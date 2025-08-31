<x-layouts.app.sidebar title="Chỉnh sửa User: {{ $user->name }}">
    <flux:main>
        <div class="p-6">
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center mb-6">
                    <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 text-gray-600 hover:text-gray-700 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-semibold text-gray-900 text-gray-900">Chỉnh sửa User: {{ $user->name }}</h1>
                </div>

                <div class="bg-white bg-white shadow-sm rounded-lg p-6">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 text-gray-700 mb-2">
                                Tên
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 text-gray-900"
                                   required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full px-3 py-2 border border-gray-300 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 text-gray-900"
                                   required>
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 text-gray-700 mb-2">
                                Mật khẩu mới (để trống nếu không thay đổi)
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="w-full px-3 py-2 border border-gray-300 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 text-gray-900">
                            @error('password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 text-gray-700 mb-2">
                                Xác nhận mật khẩu mới
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="w-full px-3 py-2 border border-gray-300 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50 text-gray-900">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 text-gray-700 mb-2">
                                Roles
                            </label>
                            <div class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto border border-gray-300 border-gray-300 rounded-md p-3 bg-gray-50">
                                @foreach($roles as $role)
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="roles[]" 
                                               value="{{ $role->name }}"
                                               {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 border-gray-300 bg-gray-50">
                                        <span class="ml-2 text-sm text-gray-700 text-gray-700">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('roles')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.users.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 bg-gray-100 hover:bg-gray-100 text-gray-800 text-gray-900 font-bold py-2 px-4 rounded">
                                Hủy
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Cập nhật User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
