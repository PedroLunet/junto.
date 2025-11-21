<!-- Modal -->
<div id="postModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50;"
  onclick="closePostModal()">
  <div
    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; max-width: 1200px; width: calc(100% - 32px); height: 600px; display: flex;"
    onclick="event.stopPropagation()">

    <!-- Left side - Post Content -->
    <div style="flex: 1; display: flex; flex-direction: column; border-right: 1px solid #ccc;">
      <!-- Header with author -->
      <div style="padding: 16px; border-bottom: 1px solid #ccc;">
        <div id="modalAuthor"></div>
      </div>

      <!-- Post Content -->
      <div id="modalContent" style="padding: 16px; flex: 1; overflow-y: auto;"></div>

      <!-- Actions (like, comment) -->
      <div style="padding: 16px; border-top: 1px solid #ccc; display: flex; gap: 16px;">
        <button onclick="likePost(event)" style="all: unset; cursor: pointer;">
          ❤️ <span id="likesCount">0</span>
        </button>
        <button onclick="focusComment()" style="all: unset; cursor: pointer;">
          💬 <span id="commentsCount">0</span>
        </button>
      </div>
    </div>

    <!-- Right side - Comments -->
    <div style="width: 400px; display: flex; flex-direction: column;">
      <!-- Close button -->
      <div style="padding: 16px; border-bottom: 1px solid #ccc; display: flex; justify-content: flex-end;">
        <button onclick="closePostModal()"
          style="all: unset; cursor: pointer; font-size: 20px; line-height: 1;">&times;</button>
      </div>

      <!-- Comments Section -->
      <div id="commentsSection" style="padding: 16px; flex: 1; overflow-y: auto;"></div>

      <!-- Add Comment -->
      <div style="padding: 16px; border-top: 1px solid #ccc;">
        <div style="display: flex; gap: 8px; align-items: center;">
          <input type="text" id="commentInput" placeholder="Add a comment..."
            style="flex: 1; padding: 8px; border: 1px solid #ccc; outline: none;"
            onkeypress="handleCommentKeyPress(event)">
          <button onclick="submitComment()"
            style="all: unset; cursor: pointer; padding: 8px 16px; border: 1px solid #000; white-space: nowrap;">Post</button>
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

    // Add post image if it exists
    if (post.image_url) {
      html += '<div style="margin-top: 16px;">';
      html += '<img src="/images/' + post.image_url + '" alt="Post image" ';
      html += 'style="width: 100%; max-width: 500px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; display: block; margin: 0 auto;">';
      html += '</div>';
    }

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

    if (!currentPostId) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/posts/${currentPostId}/like`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update likes count
          document.getElementById('likesCount').textContent = data.likes_count;

          // Optional: Change heart color based on liked state
          // You can add visual feedback here later
        }
      })
      .catch(error => {
        console.error('Error liking post:', error);
      });
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