@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">

        <h1 class="text-4xl font-bold py-10 px-20 text-gray-900">Friend Requests</h1>


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
