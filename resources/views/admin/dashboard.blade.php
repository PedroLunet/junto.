@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- stats cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 p-10">
            <x-stats-card title="Total Users" :value="$stats['totalUsers']" />
            <x-stats-card title="Active Users" :value="$stats['activeUsers']" />
            <x-stats-card title="Friendships" :value="$stats['totalFriendships']" />
            <x-stats-card title="Pending Friend Requests" :value="$stats['pendingRequests']" />
            <x-stats-card title="Total Posts" :value="$stats['totalPosts']" />
            <x-stats-card title="Standard Posts" :value="$stats['standardPosts']" />
        </div>

        <!-- media review cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 px-10">
            <x-stats-card title="Music Reviews" :value="$stats['musicReviews']" />
            <x-stats-card title="Movie Reviews" :value="$stats['movieReviews']" />
            <x-stats-card title="Book Reviews" :value="$stats['bookReviews']" />
        </div>
    </div>
@endsection
