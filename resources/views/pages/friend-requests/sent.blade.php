@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Sent Friend Requests</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($sentRequests->isEmpty())
            <div class="bg-gray-100 p-6 rounded text-center">
                <p class="text-gray-600">You have no pending sent friend requests.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($sentRequests as $sentRequest)
                    @php
                        $receiver = \App\Models\User\User::find($sentRequest->request->notification->receiverid);
                    @endphp
                    <div class="bg-white shadow rounded-lg p-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full bg-purple-200 flex items-center justify-center">
                                @if ($receiver->profilePicture)
                                    <img src="{{ asset('profile/' . $receiver->profilePicture) }}"
                                        alt="{{ $receiver->name }}" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <span
                                        class="text-lg font-bold text-purple-700">{{ substr($receiver->name, 0, 1) }}</span>
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

        <div class="mt-6">
            <a href="{{ route('friend-requests.index') }}" class="text-purple-600 hover:text-purple-800">← Back to received
                requests</a>
        </div>
    </div>
@endsection
