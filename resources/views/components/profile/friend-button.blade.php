@if ($friendButtonData['show_button'])
    @if ($friendButtonData['status'] === 'friends')
        <!-- Already friends -->
        <form id="unfriendForm-{{ $user->id }}" action="{{ route('friends.unfriend', $user->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <x-button type="button" variant="secondary" class="px-4 py-2 inline-flex items-center"
                onclick="confirmUnfriend('{{ $user->name }}', {{ $user->id }})">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
                Friends
            </x-button>
        </form>

        <script>
            function confirmUnfriend(userName, userId) {
                alertConfirm(
                    `Are you sure you want to unfriend ${userName}?`,
                    'Unfriend Confirmation'
                ).then(confirmed => {
                    if (confirmed) {
                        document.getElementById(`unfriendForm-${userId}`).submit();
                    }
                });
            }
        </script>
    @elseif($friendButtonData['status'] === 'request_sent')
        <!-- Request pending -->
        <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded inline-block">
            Friend Request Sent
        </span>
    @elseif($friendButtonData['status'] === 'request_received')
        <!-- Has received request from this user -->
        <div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded">
            <p class="mb-2">{{ $user->name }} sent you a friend request!</p>
            <div class="flex space-x-2">
                @if ($friendButtonData['received_request'])
                    <form
                        action="{{ route('friend-requests.accept', $friendButtonData['received_request']->notificationid) }}"
                        method="POST">
                        @csrf
                        <x-button type="submit" variant="primary" class="px-4 py-2">
                            Accept
                        </x-button>
                    </form>
                    <form
                        action="{{ route('friend-requests.reject', $friendButtonData['received_request']->notificationid) }}"
                        method="POST">
                        @csrf
                        <x-button type="submit" variant="danger" class="px-4 py-2">
                            Reject
                        </x-button>
                    </form>
                @endif
            </div>
        </div>
    @else
        <!-- Can send friend request -->
        <form action="{{ route('friend-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $user->id }}">
            <x-button type="submit" variant="primary" class="px-6 py-2 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Add Friend
            </x-button>
        </form>
    @endif
@endif
