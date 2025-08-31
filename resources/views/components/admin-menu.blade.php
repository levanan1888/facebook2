@if(auth()->check() && auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
<div class="space-y-1">
    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
        Quản trị hệ thống
    </h3>
    
    <!-- @can('view-facebook-data')
    <a href="{{ route('facebook.data-management.index') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('facebook.data-management.*') ? 'bg-gray-100 text-gray-900' : '' }}">
        <svg class="text-gray-400 group-hover:text-gray-500 mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        Quản lý dữ liệu Facebook
    </a>
    @endcan -->
    
    @can('user.view')
    <a href="{{ route('admin.users.index') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 text-gray-900' : '' }}">
        <svg class="text-gray-400 group-hover:text-gray-500 mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        Quản lý Users
    </a>
    @endcan
    
    @can('role.view')
    <a href="{{ route('admin.roles.index') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('admin.roles.*') ? 'bg-gray-100 text-gray-900' : '' }}">
        <svg class="text-gray-400 group-hover:text-gray-500 mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        Quản lý Roles
    </a>
    @endcan
    
    @can('permission.view')
    <a href="{{ route('admin.permissions.index') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('admin.permissions.*') ? 'bg-gray-100 text-gray-900' : '' }}">
        <svg class="text-gray-400 group-hover:text-gray-500 mr-3 flex-shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        Quản lý Permissions
    </a>
    @endcan
</div>
@endif
