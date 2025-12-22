@if ($friendButtonData['show_button'])
    @if ($friendButtonData['status'] === 'friends')
        <!-- Already friends -->
        <form id="unfriendForm-{{ $user->id }}" action="{{ route('friends.unfriend', $user->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <x-ui.button type="button" variant="secondary" title='Unfriend' class="px-4 py-2 inline-flex items-center"
                onclick="confirmUnfriend('{{ $user->name }}', {{ $user->id }})">
                <i class="fas fa-user-check w-5 h-5 mr-2"></i>
                Friends
            </x-ui.button>
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
        <x-ui.badge variant="pending" size="sm" icon="fas fa-clock">
            Friend Request Sent
        </x-ui.badge>
    @elseif($friendButtonData['status'] === 'request_received')
        <!-- Has received request from this user -->
        <a href="{{ route('friends.index') }}" class="inline-block hover:opacity-80 transition-opacity">
            <x-ui.badge variant="pending" size="lg" icon="fas fa-user-plus">
                Requested to be your friend!
            </x-ui.badge>
        </a>
    @else
        <!-- Can send friend request -->
        <form action="{{ route('friend-requests.store') }}" method="POST">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $user->id }}">
            <x-ui.button type="submit" variant="primary" class="px-6 py-2 inline-flex items-center">
                <i class="fas fa-user-plus w-5 h-5 mr-2"></i>
                Add Friend
            </x-ui.button>
        </form>
    @endif
@endif
