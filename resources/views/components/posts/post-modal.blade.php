<!-- Modal -->
<div id="postModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50;"
  onclick="closePostModal()">
  <div
    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; max-width: 1200px; width: calc(100% - 32px); height: 600px; display: flex;"
    onclick="event.stopPropagation()">

    <!-- Left side - Post Content -->
    <div style="flex: 1; display: flex; flex-direction: column; border-right: 1px solid #ccc;">
      <!-- Header with author -->
      <div style="padding: 16px; border-bottom: 1px solid #ccc; display: flex; justify-content: space-between; align-items: flex-start;">
        <div id="modalAuthor"></div>
        @auth
            <button id="modalEditButton" class="text-gray-500 hover:text-gray-700 p-1" style="display: none;">
                <i class="fas fa-edit"></i>
            </button>
        @endauth
      </div>

      <!-- Post Content -->
      <div id="modalContent" style="padding: 16px; flex: 1; overflow-y: auto;"></div>

      <!-- Actions (like, comment) -->
      <div id="postActions" style="padding: 16px; border-top: 1px solid #ccc; display: flex; gap: 16px; align-items: center;">
        <x-button onclick="likePost(event)" variant="primary">
          ❤️ <span id="likesCount">0</span>
        </x-button>
        <x-button onclick="focusComment()" variant="primary">
          💬 <span id="commentsCount">0</span>
        </x-button>
        <x-button onclick="openReportModal(event)" id="reportButton" variant="danger">
          🚩 Report
        </x-button>
      </div>
    </div>

    <!-- Right side - Comments -->
    <div style="width: 400px; display: flex; flex-direction: column;">
      <!-- Close button -->
      <div style="padding: 16px; border-bottom: 1px solid #ccc; display: flex; justify-content: flex-end;">
        <x-button onclick="closePostModal()"
          variant="primary">&times;</x-button>
      </div>

      <!-- Comments Section -->
      <div id="commentsSection" style="padding: 16px; flex: 1; overflow-y: auto;"></div>

      <!-- Add Comment -->
      <div id="addCommentSection" style="padding: 16px; border-top: 1px solid #ccc;">
        <div style="display: flex; gap: 8px; align-items: center;">
          <input type="text" id="commentInput" placeholder="Add a comment..."
            style="flex: 1; padding: 8px; border: 1px solid #ccc; outline: none;"
            onkeypress="handleCommentKeyPress(event)">
          <x-button onclick="submitComment()"
            variant="primary">Post</x-button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Report Modal -->
<div id="reportModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 60;"
  onclick="closeReportModal()">
  <div
    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; max-width: 500px; width: calc(100% - 32px); padding: 24px; border-radius: 8px;"
    onclick="event.stopPropagation()">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
      <h2 style="margin: 0; font-size: 20px; font-weight: 600;">Report Post</h2>
      <x-button onclick="closeReportModal()"
        style="all: unset; cursor: pointer; font-size: 24px; line-height: 1; color: #666;">&times;</x-button>
    </div>

    <p style="color: #666; margin-bottom: 16px;">Please provide a reason for reporting this post. Our team will review it.</p>

    <textarea id="reportReason" placeholder="Describe why you're reporting this post (minimum 10 characters)..." 
      style="width: 100%; min-height: 120px; padding: 12px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; font-family: inherit; font-size: 14px;"
      maxlength="1000"></textarea>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px;">
      <x-button onclick="closeReportModal()"
        style="all: unset; cursor: pointer; padding: 10px 20px; border: 1px solid #ccc; border-radius: 4px;">Cancel</x-button>
      <x-button onclick="submitReport()"
        style="all: unset; cursor: pointer; padding: 10px 20px; background: #dc2626; color: white; border-radius: 4px; font-weight: 500;">Submit Report</x-button>
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
    const editBtn = document.getElementById('modalEditButton');

    currentPostId = post.id;

    // Set author info in header
    authorDiv.innerHTML = '<div>' + post.author_name + '</div>' +
      '<div>@' + post.username + '</div>';

    // Set edit button if author
    if (editBtn) {
        editBtn.style.display = 'none';
        if (window.isAuthenticated && window.currentUserUsername === post.username) {
            editBtn.style.display = 'block';
            editBtn.onclick = function() {
                closePostModal();
                if (post.post_type === 'review') {
                    openEditReviewModal(
                        post.id, 
                        post.content, 
                        post.rating, 
                        post.media_title, 
                        post.media_poster, 
                        post.media_year, 
                        post.media_creator
                    );
                } else {
                    const imageUrl = post.image_url ? `/post/${post.image_url}` : '';
                    openEditModal(post.id, post.content, imageUrl);
                }
            };
        }
    }

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
      html += '<img src="/post/' + post.image_url + '" ';
      html += 'onerror="this.src=\'/post/default.jpg\'" ';
      html += 'alt="Post image" ';
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

    // Handle guest view
    if (!window.isAuthenticated) {
        document.getElementById('postActions').style.display = 'flex';
        document.getElementById('addCommentSection').style.display = 'none';
        document.getElementById('reportButton').style.display = 'none';
    } else {
        document.getElementById('postActions').style.display = 'flex';
        document.getElementById('addCommentSection').style.display = 'block';
        document.getElementById('reportButton').style.display = 'block';
    }

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

    if (!window.isAuthenticated) {
        alert('Please login to like posts.');
        return;
    }

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
    if (!window.isAuthenticated) {
        alert('Please login to comment.');
        return;
    }
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

  function openReportModal(event) {
    event.stopPropagation();
    
    if (!currentPostId) return;
    
    // Show report modal
    document.getElementById('reportModal').style.display = 'flex';
    document.getElementById('reportReason').value = '';
  }

  function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
  }

  function submitReport() {
    const reason = document.getElementById('reportReason').value.trim();

    if (!reason) {
      alert('Please provide a reason for reporting this post.');
      return;
    }

    if (reason.length < 10) {
      alert('Please provide a more detailed reason (at least 10 characters).');
      return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/posts/${currentPostId}/report`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        reason: reason
      })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closeReportModal();
          alert(data.message || 'Report submitted successfully. Thank you for helping keep our community safe.');
        } else {
          alert(data.message || 'Failed to submit report. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error submitting report:', error);
        alert('Failed to submit report. Please try again.');
      });
  }
</script>