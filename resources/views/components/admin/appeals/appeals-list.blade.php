<div class="grid space-y-6">
    @forelse($appeals as $appeal)
        <div class="appeal-item" data-status="{{ $appeal->status }}">
            <x-admin.appeals.appeal-card :appeal="$appeal" />
        </div>
    @empty
        <div class="appeals-empty-state">
            <x-ui.empty-state icon="fa-gavel" title="No Appeals Found" description="There are no appeals to review."
                height="min-h-[calc(100vh-16rem)]" />
        </div>
    @endforelse
</div>
