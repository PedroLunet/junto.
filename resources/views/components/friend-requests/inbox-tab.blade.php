<div class="space-y-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Received Requests</h3>
        @if($friendRequests->isEmpty())
            <div class="p-6 rounded bg-gray-50 text-center">
                <p class="text-gray-600">You have no pending friend requests.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($friendRequests as $friendRequest)
                    @php
                        $sender = $friendRequest->request->sender;
                    @endphp
                    <x-ui.user-card :user="$sender">
                        <div class="flex space-x-2 ml-4">
                            <form action="{{ route('friend-requests.accept', $friendRequest->requestid) }}" method="POST"
                                class="inline">
                                @csrf
                                <x-ui.button type="submit" variant="success" class="px-4 py-2">
                                    Accept
                                </x-ui.button>
                            </form>
                            <form action="{{ route('friend-requests.reject', $friendRequest->requestid) }}" method="POST"
                                class="inline">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" class="px-4 py-2">
                                    Reject
                                </x-ui.button>
                            </form>
                        </div>
                    </x-ui.user-card>
                @endforeach
            </div>
        @endif
    </div>

    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sent Requests</h3>
        @if($sentRequests->isEmpty())
            <div class="p-6 rounded bg-gray-50 text-center">
                <p class="text-gray-600">You have no pending sent friend requests.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($sentRequests as $sentRequest)
                    @php
                        $receiver = \App\Models\User\User::find($sentRequest->request->notification->receiverid);
                    @endphp
                    <x-ui.user-card :user="$receiver">
                        <div class="ml-4">
                            <form action="{{ route('friend-requests.cancel', $sentRequest->requestid) }}" method="POST"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="secondary" class="px-4 py-2">
                                    Cancel Request
                                </x-ui.button>
                            </form>
                        </div>
                    </x-ui.user-card>
                @endforeach
            </div>
        @endif
    </div>
</div>
