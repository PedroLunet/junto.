<!-- Modal -->
<div id="postModal" style="display: none;"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closePostModal()">
  <div class="bg-white max-w-2xl w-full mx-4" onclick="event.stopPropagation()">
    <!-- Header with author and close button -->
    <div class="flex justify-between items-center p-4 border-b">
      <div id="modalAuthor" class="flex flex-col"></div>
      <button onclick="closePostModal()" style="all: unset; cursor: pointer; font-size: 24px;">&times;</button>
    </div>

    <!-- Post Content -->
    <div id="modalContent" class="p-4"></div>

    <!-- Actions (like, comment) -->
    <div class="px-4 py-2 border-t border-b">
      <div class="flex gap-4">
        <button onclick="likePost(event)" style="all: unset; cursor: pointer;">
          ❤️ <span id="likesCount">0</span>
        </button>
        <button onclick="focusComment()" style="all: unset; cursor: pointer;">
          💬 <span id="commentsCount">0</span>
        </button>
      </div>
    </div>

    <!-- Comments Section -->
    <div id="commentsSection" class="p-4 max-h-64 overflow-y-auto"></div>

    <!-- Add Comment -->
    <div class="p-4 border-t">
      <input type="text" id="commentInput" placeholder="Add a comment..." class="w-full p-2 border rounded">
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
  }

  function likePost(event) {
    event.stopPropagation();
    // TODO: Implement like functionality
    console.log('Like post:', currentPostId);
  }

  function focusComment() {
    document.getElementById('commentInput').focus();
  }
</script>