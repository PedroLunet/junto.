@props(['member', 'group', 'isOwnerView', 'ownerId'])

@php 
    $isGroupOwner = (isset($ownerId) && $member->id === $ownerId); 
@endphp

<div class="flex items-center p-3 rounded-xl hover:bg-gray-50 transition-colors {{ $isGroupOwner ? 'bg-amber-50/50 border border-amber-100' : '' }}">
    <div class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-2xl mr-5 shadow-sm border shrink-0 {{ $isGroupOwner ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-indigo-50 text-indigo-600 border-indigo-100' }}">
        {{ substr($member->name, 0, 1) }}
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <p class="text-xl font-bold text-gray-900 truncate">{{ $member->name }}</p>
            @if($isGroupOwner)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 uppercase tracking-wide">
                    Owner
                </span>
            @endif
        </div>
        <div class="flex items-center text-base text-gray-500 truncate mt-1">
            <span>@ {{ $member->username }}</span>
            @if($isGroupOwner)
                <i class="fas fa-crown text-amber-400 ml-2 text-sm"></i>
            @endif
        </div>
    </div>

    @if($isOwnerView && !$isGroupOwner)
        <form action="{{ route('groups.removeMember', ['group' => $group->id, 'user' => $member->id]) }}" method="POST" class="ml-4" onsubmit="return confirm('Are you sure you want to remove this member?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:bg-red-50 p-3 rounded-full transition-colors" title="Remove from group">
                <i class="fas fa-user-minus text-xl"></i>
            </button>
        </form>
    @endif
</div>