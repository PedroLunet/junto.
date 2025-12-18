@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container mx-auto px-4 py-8 h-[calc(100vh-64px)]">
    <div class="bg-white rounded-lg shadow overflow-hidden h-full flex">
        <!-- Sidebar -->
        <div id="sidebar-container" class="hidden md:flex md:w-1/3 border-r border-gray-200 flex-col h-full">
            <x-messages.sidebar :activeChats="$activeChats" :otherFriends="$otherFriends" :activeFriendId="$friend->id" />
        </div>

        <!-- Chat Area -->
        <div id="chat-area-container" class="w-full md:w-2/3 flex flex-col h-full">
            <x-messages.chat-box :friend="$friend" :messages="$messages" />
        </div>
    </div>
</div>
@endsection

