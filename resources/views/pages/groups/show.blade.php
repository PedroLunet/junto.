@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md">
        <div class="p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold text-gray-800">{{ $group->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $group->description }}</p>
                </div>
                @if ($group->isprivate && !$group->members->contains(Auth::user()))
                    @if($pendingRequest)
                        <div class="flex gap-2">
                            <x-ui.button type="button" variant="secondary" disabled>Request Sent</x-ui.button>
                            <form method="POST" action="{{ route('groups.cancelRequest', $group) }}">
                                @csrf
                                <x-ui.button type="submit" variant="danger">Cancel Request</x-ui.button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('groups.join', $group) }}">
                            @csrf
                            <x-ui.button type="submit" variant="primary">Request to Join</x-ui.button>
                        </form>
                    @endif
                @elseif ($group->members->contains(Auth::user()))
                    <form method="POST" action="{{ route('groups.leave', $group) }}">
                        @csrf
                        <x-ui.button type="submit" variant="danger">Leave Group</x-ui.button>
                    </form>
                @else
                    <form method="POST" action="{{ route('groups.join', $group) }}">
                        @csrf
                        <x-ui.button type="submit" variant="primary">Join Group</x-ui.button>
                    </form>
                @endif
            </div>

            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Members ({{ $group->members->count() }})</h2>

                @if($friendsInGroup->isNotEmpty())
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Friends in this group:</h3>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($friendsInGroup as $friend)
                                <span class="bg-blue-100 text-blue-800 text-sm font-medium mr-2 px-2.5 py-0.5 rounded">{{ $friend->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!$group->isprivate || $group->members->contains(Auth::user()))
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($group->members as $member)
                            <div class="flex items-center space-x-4 p-2 bg-gray-100 rounded-lg">
                                <img src="{{ $member->profilepicture ? asset('storage/profile_pictures/' . $member->profilepicture) : asset('images/default-profile.png') }}" alt="{{ $member->name }}" class="w-12 h-12 rounded-full">
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $member->name }}</h3>
                                    @if($member->pivot->isowner)
                                        <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full">Owner</span>
                                    @endif
                                    <p class="text-sm text-gray-500">{{'@'}}{{ $member->username }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-8 text-center">
                        <p class="text-gray-600">This is a private group. Request to join to see the full member list and posts.</p>
                    </div>
                @endif
            </div>

            @if($isOwner && $pendingRequests->isNotEmpty())
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Pending Join Requests</h2>
                    <div class="space-y-3">
                        @foreach($pendingRequests as $request)
                            @php
                                $sender = \App\Models\User\User::find($request->senderid);
                            @endphp
                            @if($sender)
                                <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ $sender->profilepicture ? asset('storage/profile_pictures/' . $sender->profilepicture) : asset('images/default-profile.png') }}" alt="{{ $sender->name }}" class="w-12 h-12 rounded-full">
                                        <div>
                                            <h3 class="font-semibold text-gray-800">{{ $sender->name }}</h3>
                                            <p class="text-sm text-gray-500">{{'@'}}{{ $sender->username }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('groups.acceptRequest', [$group, $request->notificationid]) }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="success">Accept</x-ui.button>
                                        </form>
                                        <form method="POST" action="{{ route('groups.rejectRequest', [$group, $request->notificationid]) }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="danger">Reject</x-ui.button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


