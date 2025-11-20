<!-- Modal -->
<div id="postModal" style="display: none;"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closePostModal()">
  <div class="bg-white w-full mx-4" style="max-width: 1200px; height: 600px; display: flex;"
    onclick="event.stopPropagation()">

    <!-- Left side - Post Content -->
    <div style="flex: 1; display: flex; flex-direction: column; border-right: 1px solid #ddd;">
      <!-- Header with author -->
      <div class="flex items-center p-4 border-b">
        <div id="modalAuthor" class="flex flex-col"></div>
      </div>

      <!-- Post Content -->
      <div id="modalContent" class="p-4" style="flex: 1; overflow-y: auto;"></div>

      <!-- Actions (like, comment) -->
      <div class="px-4 py-2 border-t">
        <div class="flex gap-4">
          <button onclick="likePost(event)" style="all: unset; cursor: pointer;">
            ❤️ <span id="likesCount">0</span>
          </button>
          <button onclick="focusComment()" style="all: unset; cursor: pointer;">
            💬 <span id="commentsCount">0</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Right side - Comments -->
    <div style="width: 400px; display: flex; flex-direction: column;">
      <!-- Close button -->
      <div class="flex justify-end p-4 border-b">
        <button onclick="closePostModal()" style="all: unset; cursor: pointer; font-size: 24px;">&times;</button>
      </div>

      <!-- Comments Section -->
      <div id="commentsSection" class="p-4" style="flex: 1; overflow-y: auto;"></div>

      <!-- Add Comment -->
      <div class="p-4 border-t">
        <div style="display: flex; gap: 8px;">
          <input type="text" id="commentInput" placeholder="Add a comment..." class="p-2 border rounded"
            style="flex: 1;" onkeypress="handleCommentKeyPress(event)">
          <button onclick="submitComment()"
            style="all: unset; cursor: pointer; padding: 8px 16px; background: #0095f6; color: white; border-radius: 4px; font-weight: 600;">Post</button>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  let currentPostId = null;

  function openPostModal(post) {
    const modal = document.getElementById('postModal');
    const content = document.getElementById('modalContent');
    const authorDiv = document.getElementById('modalAuthor');
    const commentsSection = document.getElementById('commentsSection');

    currentPostId = post.id;

    // Set author info in header
    authorDiv.innerHTML = '<div>' + post.author_name + '</div>' +
      '<div>@' + post.username + '</div>';

    // Set content
    let html = '<div>';

    if (post.rating) {
      html += '<div class="mb-2">';
      html += '⭐ ' + post.rating + '/5 - ' + post.media_title;
      html += '</div>';
    }

    html += '<div class="mb-2">';
    html += post.content;
    html += '</div>';

    if (post.created_at) {
      html += '<div style="color: #888; font-size: 0.9em;">';
      html += new Date(post.created_at).toLocaleString();
      html += '</div>';
    }

    html += '</div>';

    content.innerHTML = html;

    // Set likes count
    document.getElementById('likesCount').textContent = post.likes_count || 0;
    document.getElementById('commentsCount').textContent = post.comments_count || 0;

    // Load comments from server
    loadComments(post.id);

    modal.style.display = 'flex';
  }

  function loadComments(postId) {
    const commentsSection = document.getElementById('commentsSection');
    commentsSection.innerHTML = '<div style="color: #888;">Loading comments...</div>';

    fetch(`/posts/${postId}/comments`)
      .then(response => response.json())
      .then(comments => {
        if (comments.length === 0) {
          commentsSection.innerHTML = '<div style="color: #888;">No comments yet</div>';
          return;
        }

        let html = '';
        comments.forEach(comment => {
          html += '<div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eee;">';
          html += '<div style="display: flex; justify-content: space-between;">';
          html += '<div>';
          html += '<strong>' + comment.author_name + '</strong> ';
          html += '<span style="color: #666;">@' + comment.username + '</span>';
          html += '</div>';
          html += '<div style="font-size: 0.85em; color: #888;">';
          html += '❤️ ' + (comment.likes_count || 0);
          html += '</div>';
          html += '</div>';
          html += '<div style="margin-top: 4px;">' + comment.content + '</div>';
          html += '<div style="font-size: 0.85em; color: #888; margin-top: 4px;">';
          html += new Date(comment.created_at).toLocaleString();
          html += '</div>';
          html += '</div>';
        });

        commentsSection.innerHTML = html;
      })
      .catch(error => {
        console.error('Error loading comments:', error);
        commentsSection.innerHTML = '<div style="color: #888;">Error loading comments</div>';
      });
  }

  function closePostModal() {
    const modal = document.getElementById('postModal');
    modal.style.display = 'none';
    currentPostId = null;

    // Clear the comment input
    document.getElementById('commentInput').value = '';
  }

  function likePost(event) {
    event.stopPropagation();
    // TODO: Implement like functionality
    console.log('Like post:', currentPostId);
  }

  function focusComment() {
    document.getElementById('commentInput').focus();
  }

  function handleCommentKeyPress(event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      submitComment();
    }
  }

  function submitComment() {
    const input = document.getElementById('commentInput');
    const commentText = input.value.trim();

    if (!commentText) {
      return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Submit comment to backend
    fetch(`/posts/${currentPostId}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        content: commentText
      })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Clear input
          input.value = '';

          // Reload comments to show the new one
          loadComments(currentPostId);

          // Update comment count
          const countElem = document.getElementById('commentsCount');
          countElem.textContent = parseInt(countElem.textContent) + 1;
        }
      })
      .catch(error => {
        console.error('Error submitting comment:', error);
        alert('Failed to post comment. Please try again.');
      });
  }
</script>