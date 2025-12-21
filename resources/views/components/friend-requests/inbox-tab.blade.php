<div class="space-y-4 sm:space-y-6">
    <div>
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Received Requests</h3>
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
                        <div class="flex flex-col sm:flex-row gap-2 ml-0 sm:ml-4 w-full sm:w-auto">
                            <form action="{{ route('friend-requests.accept', $friendRequest->requestid) }}" method="POST"
                                class="flex-1 sm:flex-none">
                                @csrf
                                <x-ui.button type="submit" variant="success" class="px-3 sm:px-4 py-2 w-full sm:w-auto text-sm sm:text-base">
                                    Accept
                                </x-ui.button>
                            </form>
                            <form action="{{ route('friend-requests.reject', $friendRequest->requestid) }}" method="POST"
                                class="flex-1 sm:flex-none">
                                @csrf
                                <x-ui.button type="submit" variant="secondary" class="px-3 sm:px-4 py-2 w-full sm:w-auto text-sm sm:text-base">
                                    Reject
                                </x-ui.button>
                            </form>
                        </div>
                    </x-ui.user-card>
                @endforeach
            </div>
        @endif
    </div>

    <div class="border-t pt-4 sm:pt-6">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Sent Requests</h3>
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
                        <div class="ml-0 sm:ml-4 w-full sm:w-auto">
                            <form action="{{ route('friend-requests.cancel', $sentRequest->requestid) }}" method="POST"
                                class="w-full">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="secondary" class="px-3 sm:px-4 py-2 w-full sm:w-auto text-sm sm:text-base">
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
