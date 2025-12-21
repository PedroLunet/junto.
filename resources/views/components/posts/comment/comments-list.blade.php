@if (empty($comments))
    <x-ui.empty-state icon="fa-comment-dots" title="No Comments" description="There are no comments to display."
        height="min-h-[200px]" />
@else
    @foreach ($comments as $comment)
        <x-posts.comment.comment :comment="$comment" />
    @endforeach
@endif
