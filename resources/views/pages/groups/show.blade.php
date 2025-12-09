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
                @if ($group->members->contains(Auth::user()))
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
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($group->members as $member)
                        <div class="flex items-center space-x-4 p-2 bg-gray-100 rounded-lg">
                            <img src="{{ $member->profilepicture ? asset('storage/profile_pictures/' . $member->profilepicture) : asset('images/default-profile.png') }}" alt="{{ $member->name }}" class="w-12 h-12 rounded-full">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $member->name }}</h3>
                                <p class="text-sm text-gray-500">{{'@'}}{{ $member->username }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

