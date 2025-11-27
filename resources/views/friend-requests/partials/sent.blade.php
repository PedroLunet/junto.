@if ($sentRequests->isEmpty())
    <div class="p-6 rounded text-center">
        <p class="text-gray-600">You have no pending sent friend requests.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($sentRequests as $sentRequest)
            @php
                $receiver = \App\Models\User::find($sentRequest->request->notification->receiverid);
            @endphp
            <div class="bg-white shadow rounded-lg p-6 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-purple-200 flex items-center justify-center">
                        @if ($receiver->profilepicture)
                            <img src="{{ asset($receiver->profilepicture) }}" alt="{{ $receiver->name }}"
                                class="w-12 h-12 rounded-full object-cover">
                        @else
                            <span class="text-lg font-bold text-purple-700">{{ substr($receiver->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">{{ $receiver->name }}</h3>
                        <p class="text-gray-600 text-sm">@<!-- -->{{ $receiver->username }}</p>
                        <p class="text-gray-500 text-xs">Request pending</p>
                    </div>
                </div>
                <div>
                    <form action="{{ route('friend-requests.cancel', $sentRequest->requestid) }}" method="POST"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Cancel Request
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
