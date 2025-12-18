@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container mx-auto px-4 py-8 h-[calc(100vh-64px)]">
    <div class="bg-white rounded-lg shadow overflow-hidden h-full flex">
        <!-- Sidebar -->
        <div class="w-full md:w-1/3 h-full">
            <x-messages.sidebar :friends="$friends" />
        </div>

        <!-- Main Content Area (Placeholder) -->
        <div class="hidden md:flex md:w-2/3 flex-col items-center justify-center bg-gray-50 text-gray-500">
            <div class="text-center">
                <div class="bg-gray-200 rounded-full p-6 inline-block mb-4">
                    <i class="fa-regular fa-comments text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-700">Your Messages</h3>
                <p class="mt-2">Select a conversation to start chatting</p>
            </div>
        </div>
    </div>
</div>
@endsection
