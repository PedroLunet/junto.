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
                <x-slot name="actions">
                    <div class="flex flex-col sm:flex-row gap-2 ml-0 sm:ml-4 w-full sm:w-auto mt-2 sm:mt-0">
                        <form action="{{ route('friend-requests.cancel', $sentRequest->requestid) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="secondary"
                                class="w-full sm:w-auto px-3 py-1.5 text-base sm:text-lg">
                                Cancel Request
                            </x-ui.button>
                        </form>
                    </div>
                </x-slot>
            </x-ui.user-card>
        @endforeach
    </div>
@endif
