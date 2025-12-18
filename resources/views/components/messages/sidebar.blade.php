@props(['friends', 'activeFriendId' => null])

<div class="w-full bg-white border-r border-gray-200 flex flex-col h-full">
    <div class="p-4 border-b border-gray-200">
        <!-- vai ter search aqui-->
    </div>
    
    <div class="overflow-y-auto flex-1">
        @if($friends->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>No friends yet.</p>
                <a href="{{ route('search.users') }}" class="text-purple-600 hover:underline mt-2 inline-block">Find friends</a>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($friends as $friend)
                    <li>
                        <a href="{{ route('messages.show', $friend->id) }}" 
                           class="block hover:bg-gray-50 transition duration-150 ease-in-out {{ $activeFriendId == $friend->id ? 'bg-purple-50 border-l-4 border-purple-600' : '' }}">
                            <div class="flex items-center px-4 py-4 sm:px-6">
                                <div class="min-w-0 flex-1 flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $friend->getProfileImage() }}" alt="{{ $friend->name }}">
                                    </div>
                                    <div class="min-w-0 flex-1 px-4">
                                        <p class="text-sm font-medium {{ $activeFriendId == $friend->id ? 'text-purple-700' : 'text-gray-900' }} truncate">{{ $friend->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">@ {{ $friend->username }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
