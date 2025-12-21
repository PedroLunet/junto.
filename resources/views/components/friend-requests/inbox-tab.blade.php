<div class="space-y-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Received Requests</h3>
        <x-friend-requests.received-requests :friendRequests="$friendRequests" />
    </div>

    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sent Requests</h3>
        <x-friend-requests.sent-requests :sentRequests="$sentRequests" />
    </div>
</div>
