<div id="user-cards-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($users as $user)
        <div class="user-card">
            <x-admin.users.user-card :user="$user" />
        </div>
    @empty
        <x-ui.empty-state icon="fa-users" title="No users found" description="There are no users to display."
            height="min-h-[200px]" class="col-span-full" />
    @endforelse
</div>
