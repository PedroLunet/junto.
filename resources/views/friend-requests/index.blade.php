@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-10 gap-10">
            <button onclick="history.back()" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left text-4xl"></i>
            </button>
            <h1 class="text-4xl font-bold">Friend Requests</h1>
        </div>

        <x-tabs :tabs="[
            'received' => [
                'title' => 'Received',
                'content' => view('friend-requests.partials.received', [
                    'friendRequests' => $friendRequests,
                ])->render(),
            ],
            'sent' => [
                'title' => 'Sent',
                'content' => view('friend-requests.partials.sent', [
                    'sentRequests' => $sentRequests,
                ])->render(),
            ],
        ]" />
    </div>
@endsection
