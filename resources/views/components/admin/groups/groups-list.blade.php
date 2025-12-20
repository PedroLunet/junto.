<div class="space-y-6">
    @forelse($groups as $group)
        <x-admin.groups.group-card :group="$group" />
    @empty
        <x-ui.empty-state icon="fa-users" title="No Groups Found" description="There are no groups to display."
            height="min-h-[calc(100vh-16rem)]" />
    @endforelse
</div>
