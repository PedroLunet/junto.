@props(['user'])

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 user-card">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-900 leading-tight">{{ $user->name }}</h3>
        </div>
        
        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wide {{ $user->isblocked ? 'bg-red-100 text-red-800' : ($user->isadmin ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
            {{ $user->isblocked ? 'Blocked' : ($user->isadmin ? 'Admin' : 'Active') }}
        </span>
    </div>

    <div class="space-y-4 mb-6">
        <div class="flex justify-between border-b border-gray-50 pb-2">
            <span class="text-gray-500 text-sm">Username</span>
            <span class="text-gray-900 font-medium text-sm">{{ $user->username }}</span>
        </div>
        
        <div class="flex justify-between border-b border-gray-50 pb-2">
            <span class="text-gray-500 text-sm">Email</span>
            <span class="text-gray-900 font-medium text-sm">{{ $user->email }}</span>
        </div>

        <div class="flex justify-between border-b border-gray-50 pb-2">
            <span class="text-gray-500 text-sm">Joined Date</span>
            <span class="text-gray-900 font-medium text-sm">
                {{ $user->createdat ? \Carbon\Carbon::parse($user->createdat)->format('M d, Y') : 'N/A' }}
            </span>
        </div>
    </div>

    <div class="flex justify-center gap-6">
        <x-ui.icon-button variant="blue" class="edit-user-btn py-2.5" title="Edit"
            data-user-id="{{ $user->id }}"
            data-user-name="{{ $user->name }}" 
            data-user-username="{{ $user->username }}"
            data-user-email="{{ $user->email }}" 
            data-user-bio="{{ $user->bio }}"
            data-user-isadmin="{{ $user->isadmin ? 'true' : 'false' }}">
            <i class="fas fa-edit"></i>
        </x-ui.icon-button>

        <x-ui.icon-button variant="yellow" class="ban-user-btn py-2.5" title="{{ $user->isblocked ? 'Unblock' : 'Block' }}"
            data-user-id="{{ $user->id }}"
            data-user-name="{{ $user->name }}"
            data-user-blocked="{{ $user->isblocked ? 'true' : 'false' }}">
            <i class="fas fa-ban"></i>
        </x-ui.icon-button>

        <x-ui.icon-button variant="red" class="delete-user-btn p-2.5" data-user-id="{{ $user->id }}" title="Delete">
            <i class="fas fa-trash"></i>
        </x-ui.icon-button>
    </div>
</div>