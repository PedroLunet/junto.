@if ($friendRequests->isEmpty())
    <div class="p-6 rounded text-center">
        <p class="text-gray-600">You have no pending friend requests.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($friendRequests as $friendRequest)
            @php
                $sender = $friendRequest->request->sender;
            @endphp
            <x-user-card :user="$sender">
                <div class="flex space-x-2 ml-4">
                    <form action="{{ route('friend-requests.accept', $friendRequest->requestid) }}" method="POST"
                        class="inline">
                        @csrf
                        <x-ui.button type="submit" variant="success" class="px-4 py-2 text-2xl">
                            Accept
                            </x-button>
                    </form>
                    <form action="{{ route('friend-requests.reject', $friendRequest->requestid) }}" method="POST"
                        class="inline">
                        @csrf
                        <x-ui.button type="submit" variant="danger" class="px-4 py-2 text-2xl">
                            Reject
                            </x-button>
                    </form>
                </div>
            </x-user-card>
        @endforeach
    </div>
@endif
