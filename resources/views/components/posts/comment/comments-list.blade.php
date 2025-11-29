@foreach ($comments as $comment)
    <x-posts.comment.comment :comment="$comment" />
@endforeach
