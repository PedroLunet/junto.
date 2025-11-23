@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Friend Requests</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($friendRequests->isEmpty())
        <div class="bg-gray-100 p-6 rounded text-center">
            <p class="text-gray-600">You have no pending friend requests.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($friendRequests as $friendRequest)
                @php
                    $sender = $friendRequest->request->sender;
                @endphp
                <div class="bg-white shadow rounded-lg p-6 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-purple-200 flex items-center justify-center">
                            @if($sender->profilepicture)
                                <img src="{{ asset($sender->profilepicture) }}" alt="{{ $sender->name }}" class="w-12 h-12 rounded-full object-cover">
                            @else
                                <span class="text-lg font-bold text-purple-700">{{ substr($sender->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">{{ $sender->name }}</h3>
                            <p class="text-gray-600 text-sm">@<!-- -->{{ $sender->username }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <form action="{{ route('friend-requests.accept', $friendRequest->requestid) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                Accept
                            </button>
                        </form>
                        <form action="{{ route('friend-requests.reject', $friendRequest->requestid) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('friend-requests.sent') }}" class="text-purple-600 hover:text-purple-800">View sent requests</a>
    </div>
</div>
@endsection
