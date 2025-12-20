@if ($sentRequests->isEmpty())
    <div class="p-6 rounded text-center">
        <p class="text-gray-600">You have no pending sent friend requests.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($sentRequests as $sentRequest)
            @php
                $receiver = \App\Models\User\User::find($sentRequest->request->notification->receiverid);
            @endphp
            <x-ui.user-card :user="$receiver">
                <div class="ml-4">
                    <form action="{{ route('friend-requests.cancel', $sentRequest->requestid) }}" method="POST"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="secondary" class="px-4 py-2 text-2xl">
                            Cancel Request
                        </x-ui.button>
                    </form>
                </div>
                </x-user-card>
        @endforeach
    </div>
@endif
