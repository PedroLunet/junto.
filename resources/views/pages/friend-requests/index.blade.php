@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">

        <h1 class="text-4xl font-bold py-10 px-20 text-gray-900">Friend Requests</h1>


        <x-tabs :tabs="[
            'received' => [
                'title' => 'Received',
                'content' => view('components.friend-requests.received-requests', [
                    'friendRequests' => $friendRequests,
                ])->render(),
            ],
            'sent' => [
                'title' => 'Sent',
                'content' => view('components.friend-requests.sent-requests', [
                    'sentRequests' => $sentRequests,
                ])->render(),
            ],
        ]" />
    </div>
@endsection
