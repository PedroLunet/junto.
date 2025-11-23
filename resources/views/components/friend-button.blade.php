@php
    $isSelf = Auth::id() === $user->id;
    $isFriend = !$isSelf && Auth::user()->isFriendsWith($user->id);
    $hasSentRequest = !$isSelf && !$isFriend && Auth::user()->hasSentFriendRequestTo($user->id);
    $hasReceivedRequest = !$isSelf && !$isFriend && Auth::user()->hasReceivedFriendRequestFrom($user->id);
@endphp

@if(!$isSelf)
    @if($isFriend)
        <!-- Already friends -->
        <div class="flex space-x-2">
            <span class="bg-green-100 text-green-800 px-4 py-2 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Friends
            </span>
            <form action="{{ route('friends.unfriend', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to unfriend {{ $user->name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                    Unfriend
                </button>
            </form>
        </div>
    @elseif($hasSentRequest)
        <!-- Request pending -->
        <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded inline-block">
            Friend Request Sent
        </span>
    @elseif($hasReceivedRequest)
        <!-- Has received request from this user -->
        <div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded">
            <p class="mb-2">{{ $user->name }} sent you a friend request!</p>
            <div class="flex space-x-2">
                @php
                    $receivedRequest = \App\Models\Request::where('senderid', $user->id)
                        ->whereHas('notification', function($query) {
                            $query->where('receiverid', Auth::id());
                        })
                        ->where('status', 'pending')
                        ->whereHas('friendRequest')
                        ->first();
                @endphp
                @if($receivedRequest)
                    <form action="{{ route('friend-requests.accept', $receivedRequest->notificationid) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Accept
                        </button>
                    </form>
                    <form action="{{ route('friend-requests.reject', $receivedRequest->notificationid) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                            Reject
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @else
        <!-- Can send friend request -->
        <form action="{{ route('friend-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $user->id }}">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Add Friend
            </button>
        </form>
    @endif
@endif
