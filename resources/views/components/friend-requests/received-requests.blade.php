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
            <x-ui.user-card :user="$sender">
                <x-slot name="actions">
                    <div class="flex flex-col sm:flex-row gap-2 ml-0 sm:ml-4 w-full sm:w-auto mt-2 sm:mt-0">
                        <form action="{{ route('friend-requests.accept', $friendRequest->requestid) }}" method="POST"
                            class="inline w-full sm:w-auto">
                            @csrf
                            <x-ui.button type="submit" variant="success"
                                class="w-full sm:w-auto px-3 py-1.5 text-base sm:text-lg">
                                Accept
                            </x-ui.button>
                        </form>
                        <form action="{{ route('friend-requests.reject', $friendRequest->requestid) }}" method="POST"
                            class="inline w-full sm:w-auto">
                            @csrf
                            <x-ui.button type="submit" variant="danger"
                                class="w-full sm:w-auto px-3 py-1.5 text-base sm:text-lg">
                                Reject
                            </x-ui.button>
                        </form>
                    </div>
                </x-slot>
            </x-ui.user-card>
        @endforeach
    </div>
@endif
