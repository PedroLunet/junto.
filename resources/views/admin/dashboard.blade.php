@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- stats cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 p-10">
            <!-- total users -->
            <div class="bg-white rounded-3xl shadow p-10 text-center">
                <p class="text-lg font-medium text-gray-500 uppercase tracking-wide mb-3">Total Users</p>
                <p class="text-6xl font-bold text-gray-900">{{ number_format($stats['totalUsers']) }}</p>
            </div>

            <!-- active users (non-blocked) -->
            <div class="bg-white rounded-3xl shadow p-10 text-center">
                <p class="text-lg font-medium text-gray-500 uppercase tracking-wide mb-3">Active Users</p>
                <p class="text-6xl font-bold text-gray-900">{{ number_format($stats['activeUsers']) }}</p>
            </div>

            <!-- friendships -->
            <div class="bg-white rounded-3xl shadow p-10 text-center">
                <p class="text-lg font-medium text-gray-500 uppercase tracking-wide mb-3">Friendships</p>
                <p class="text-6xl font-bold text-gray-900">{{ number_format($stats['totalFriendships']) }}</p>
            </div>

            <!-- pending friend requests -->
            <div class="bg-white rounded-3xl shadow p-10 text-center">
                <p class="text-lg font-medium text-gray-500 uppercase tracking-wide mb-3">Pending Friend Requests</p>
                <p class="text-6xl font-bold text-gray-900">{{ number_format($stats['pendingRequests']) }}</p>
            </div>
        </div>
    </div>
@endsection
