<!-- Modal -->
<div id="postModal" class="fixed inset-0 z-50 hidden" onclick="closePostModal()">
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <!-- Modal panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-5xl h-[80vh] flex flex-col sm:flex-row"
                onclick="event.stopPropagation()">

                <!-- Left Side: Post Content -->
                <div class="flex-1 flex flex-col sm:border-r border-gray-200 overflow-hidden bg-white">
                    <!-- Header -->
                    <div class="p-6 flex justify-between items-center shrink-0">
                        <div id="modalAuthor" class="flex items-center gap-3">
                            <!-- js will inject author info here -->
                        </div>
                        @auth
                            <button id="modalEditButton"
                                class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100 hidden">
                                <i class="fas fa-edit text-xl"></i>
                            </button>
                        @endauth
                    </div>

                    <!-- Content -->
                    <div id="modalContent" class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                        <!-- JS will inject content here -->
                    </div>

                    <!-- Actions Footer -->
                    <div id="postActions"
                        class="h-20 px-6 border-t border-gray-200 flex items-center gap-6 shrink-0 relative z-10">
                        <!-- Like Button -->
                        <button onclick="likePost(event)"
                            class="group flex items-center gap-2 text-gray-600 hover:text-red-500 transition-colors">
                            <i class="far fa-heart text-xl group-hover:scale-110 transition-transform"
                                id="modalLikeIcon"></i>
                            <span id="likesCount" class="text-base font-medium">0</span>
                        </button>

                        <!-- Comment Indicator -->
                        <div class="flex items-center gap-2 text-gray-600">
                            <i class="far fa-comment text-xl"></i>
                            <span id="commentsCount" class="text-base font-medium">0</span>
                        </div>

                        <!-- Report Button -->
                        <button onclick="openReportModal(event)" id="reportButton"
                            class="ml-auto text-gray-500 hover:text-red-600 transition-colors flex items-center gap-2 px-3 py-1.5 rounded-xl hover:bg-red-50">
                            <i class="fas fa-flag"></i>
                            <span>Report</span>
                        </button>
                    </div>
                </div>

                <!-- Right Side: Comments  -->
                <div class="w-full sm:w-[400px] flex flex-col bg-gray-50 h-full border-l border-gray-200">
                    <!-- Header with Close -->
                    <div
                        class="p-4 border-b border-gray-200 flex justify-between items-center shrink-0 bg-white shadow-sm z-10">
                        <h3 class="font-semibold text-gray-900 text-xl">Comments</h3>
                        <x-ui.button onclick="closePostModal()" variant="ghost">
                            <i class="fas fa-times text-lg"></i>
                        </x-ui.button>
                    </div>

                    <!-- Comments List -->
                    <div id="commentsSection" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                        @isset($comments)
                            <x-posts.comment.comments-list :comments="$comments" />
                        @endisset
                    </div>

                    <!-- Add Comment Input -->
                    <div id="addCommentSection"
                        class="h-20 px-6 py-4 border-t border-gray-200 bg-white shrink-0 flex items-center">
                        <div class="flex gap-3 items-center w-full">
                            @php
                                $currentUserProfilePicture =
                                    Auth::check() && Auth::user()->profilePicture
                                    ? asset('profile/' . Auth::user()->profilePicture)
                                    : asset('profile/default.png');
                            @endphp
                            <img src="{{ $currentUserProfilePicture }}" alt="User Avatar"
                                class="w-8 h-8 rounded-full object-cover shrink-0">
                            <div class="flex-1 relative">
                                <input type="text" id="commentInput"
                                    class="w-full h-10 rounded-full border-gray-300 bg-gray-100 pl-4 pr-12 focus:border-[#38157a] focus:ring-[#38157a] focus:bg-white transition-all text-sm"
                                    placeholder="Write a comment..." onkeypress="handleCommentKeyPress(event)">
                                <button onclick="submitComment()"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-[#38157a] hover:text-[#5a3d8a] p-1.5 rounded-full hover:bg-purple-50 transition-colors">
                                    <i class="fas fa-paper-plane text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Report Modal Component -->
<x-posts.report.report-modal />

<script>
    let currentPostId = null;
    let commentRefreshInterval = null;

    function convertMentionsToLinks(text, taggedUsers) {
        if (!text || !taggedUsers || taggedUsers.length === 0) {
            return escapeHtml(text);
        }

        let result = escapeHtml(text);

        taggedUsers.forEach(user => {
            const mention = '@' + user.name;
            const link =
                `<a href="/${user.username}" class="text-[#38157a] font-semibold hover:underline">${escapeHtml(user.name)}</a>`;
            result = result.replace(escapeHtml(mention), link);
        });

        return result;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.openPostModal = function (post) {
        const modal = document.getElementById('postModal');
        const content = document.getElementById('modalContent');
        const authorDiv = document.getElementById('modalAuthor');
        const commentsSection = document.getElementById('commentsSection');
        const editBtn = document.getElementById('modalEditButton');
        const likeIcon = document.getElementById('modalLikeIcon');
        currentPostId = post.id;
        authorDiv.innerHTML = `
            <a href="/${post.username}" class="shrink-0">
                <img src="${post.author_image ? '/profile/' + post.author_image : '/profile/default.png'}" 
                     alt="User Avatar" 
                     class="w-10 h-10 rounded-full object-cover"
                     onerror="this.onerror=null; this.src='/profile/default.png';">
            </a>
            <div class="flex flex-col">
                <a href="/${post.username}" class="font-semibold text-black text-lg hover:text-[#38157a] transition">${post.author_name}</a>
                <a href="/${post.username}" class="text-gray-600 text-sm">@${post.username}</a>
            </div>
        `;
        if (editBtn) {
            editBtn.style.display = 'none';
            if (window.isAuthenticated && window.currentUserUsername === post.username) {
                editBtn.style.display = 'block';
                editBtn.onclick = function () {
                    window.closePostModal();
                    if (post.post_type === 'review') {
                        window.openEditReviewModal(
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
                        window.openEditModal(post.id, post.content, imageUrl);
                    }
                };
            }
        }
        let html = '';
        if (post.post_type === 'review') {
            let starsHtml = '';
            for (let i = 0; i < post.rating; i++) {
                starsHtml += '<i class="fas fa-star text-lg"></i>';
            }
            const reviewContent = post.content ? convertMentionsToLinks(post.content, post.tagged_users) : '';
            html = `
            <div class="flex flex-col sm:flex-row gap-6">
                <div class="shrink-0 mx-auto sm:mx-0">
                    <img src="${post.media_poster}" 
                         class="rounded-xl shadow-lg object-cover ${post.media_type === 'music' ? 'w-48 h-48' : 'w-48 h-72'}" 
                         alt="${post.media_title}">
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-2">
                        <h3 class="text-2xl font-bold text-gray-900 leading-tight">${post.media_title}</h3>
                        <div class="text-yellow-400 shrink-0 mt-1">
                            ${starsHtml}
                        </div>
                    </div>
                    <p class="text-base text-gray-600 font-medium mb-1">${post.media_creator}</p>
                    <p class="text-sm text-gray-500 mb-4">${post.media_year}</p>
                    ${reviewContent ? `<p class="text-black">${reviewContent}</p>` : ''}
                </div>
            </div>
        `;
        } else {
            if (post.image_url) {
                html += `
                <div class="w-full bg-gray-100 rounded-xl overflow-hidden mb-6 shadow-inner">
                    <img src="/post/${post.image_url}" 
                         onerror="this.src='/post/default.jpg'" 
                         alt="Post image" 
                         class="w-full h-auto object-contain max-h-[400px] mx-auto">
                </div>
            `;
            }
            if (post.content) {
                const contentWithMentions = convertMentionsToLinks(post.content, post.tagged_users);
                html += `<p class="text-black whitespace-pre-wrap">${contentWithMentions}</p>`;
            }
        }
        if (post.created_at) {
            const date = new Date(post.created_at);
            html += `
            <div class="mt-6 pt-4 text-gray-500 text-xs flex items-center gap-2">
                <i class="far fa-clock"></i>
                ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} · ${date.toLocaleDateString()}
            </div>
        `;
        }
        content.innerHTML = html;

        // Sync like status from feed if available
        const feedLikeBtn = document.getElementById(`like-btn-${post.id}`);
        let isLiked = post.is_liked;
        let likesCount = post.likes_count || 0;

        if (feedLikeBtn) {
            isLiked = feedLikeBtn.classList.contains('text-red-500');
            const feedLikeCount = document.getElementById(`like-count-${post.id}`);
            if (feedLikeCount) {
                likesCount = feedLikeCount.textContent;
            }
        }

        const likesCountElem = document.getElementById('likesCount');
        likesCountElem.textContent = likesCount;
        document.getElementById('commentsCount').textContent = post.comments_count || 0;
        if (isLiked) {
            likeIcon.classList.remove('far');
            likeIcon.classList.add('fas', 'text-red-500');
            likesCountElem.classList.add('text-red-500');
        } else {
            likeIcon.classList.remove('fas', 'text-red-500');
            likeIcon.classList.add('far');
            likesCountElem.classList.remove('text-red-500');
        }
        window.loadComments(post.id);
        if (!window.isAuthenticated) {
            document.getElementById('postActions').style.display = 'flex';
            document.getElementById('addCommentSection').style.display = 'none';
            document.getElementById('reportButton').style.display = 'none';
        } else {
            document.getElementById('postActions').style.display = 'flex';
            document.getElementById('addCommentSection').style.display = 'block';
            document.getElementById('reportButton').style.display = 'flex';
        }
        modal.classList.remove('hidden');

        if (commentRefreshInterval) {
            clearInterval(commentRefreshInterval);
        }
        commentRefreshInterval = setInterval(() => {
            if (currentPostId) {
                window.loadComments(currentPostId, true);
            }
        }, 5000);
    }

    window.loadComments = function (postId, silent = false) {
        const commentsSection = document.getElementById('commentsSection');

        if (!silent) {
            commentsSection.innerHTML = `
            <div class="flex justify-center items-center py-8 text-gray-400">
                <i class="fas fa-circle-notch fa-spin text-2xl"></i>
            </div>
        `;
        }

        fetch(`/posts/${postId}/comments`)
            .then(response => response.text())
            .then(html => {
                commentsSection.innerHTML = html;

                const commentElements = commentsSection.querySelectorAll('[data-comment-id]');
                const actualCount = commentElements.length;

                const modalCountElem = document.getElementById('commentsCount');
                if (modalCountElem) {
                    modalCountElem.textContent = actualCount;
                }

                const feedCountElem = document.getElementById(`comment-count-${postId}`);
                if (feedCountElem) {
                    feedCountElem.textContent = actualCount;
                }
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                if (!silent) {
                    commentsSection.innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <p>Error loading comments</p>
                        <button onclick="window.loadComments(${postId})" class="text-sm underline mt-2">Try again</button>
                    </div>
                `;
                }
            });
    }
    window.closePostModal = function () {
        const modal = document.getElementById('postModal');
        modal.classList.add('hidden');
        currentPostId = null;
        document.getElementById('commentInput').value = '';

        if (commentRefreshInterval) {
            clearInterval(commentRefreshInterval);
            commentRefreshInterval = null;
        }
    }
    window.likePost = function (event) {
        event.stopPropagation();
        if (!currentPostId) return;
        if (!window.isAuthenticated) {
            window.location.href = '/login';
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const likeIcon = document.getElementById('modalLikeIcon');
        const likesCount = document.getElementById('likesCount');
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
                    likesCount.textContent = data.likes_count;
                    if (data.liked) {
                        likeIcon.classList.remove('far');
                        likeIcon.classList.add('fas', 'text-red-500');
                        likesCount.classList.add('text-red-500');
                    } else {
                        likeIcon.classList.remove('fas', 'text-red-500');
                        likeIcon.classList.add('far');
                        likesCount.classList.remove('text-red-500');
                    }
                    const timelineBtn = document.getElementById(`like-btn-${currentPostId}`);
                    if (timelineBtn) {
                        const timelineCount = document.getElementById(`like-count-${currentPostId}`);
                        const timelineIcon = document.getElementById(`like-icon-${currentPostId}`);
                        if (timelineCount) timelineCount.textContent = data.likes_count;
                        if (data.liked) {
                            timelineBtn.classList.remove('text-gray-600', 'focus:text-gray-600');
                            timelineBtn.classList.add('text-red-500', 'focus:text-red-500');
                            if (timelineIcon) {
                                timelineIcon.classList.remove('far');
                                timelineIcon.classList.add('fas');
                            }
                        } else {
                            timelineBtn.classList.remove('text-red-500', 'focus:text-red-500');
                            timelineBtn.classList.add('text-gray-600', 'focus:text-gray-600');
                            if (timelineIcon) {
                                timelineIcon.classList.remove('fas');
                                timelineIcon.classList.add('far');
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error liking post:', error);
            });
    }
    window.handleCommentKeyPress = function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            window.submitComment();
        }
    }
    window.submitComment = function () {
        const input = document.getElementById('commentInput');
        const commentText = input.value.trim();
        if (!commentText) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
                    input.value = '';
                    window.loadComments(currentPostId);
                }
            })
            .catch(error => {
                console.error('Error submitting comment:', error);
                alert('Failed to post comment. Please try again.');
            });
    }
    window.openReportModal = function (event) {
        event.stopPropagation();
        if (!currentPostId) return;
        document.getElementById('reportModal').classList.remove('hidden');
        document.getElementById('reportReason').value = '';
    }
    window.closeReportModal = function () {
        document.getElementById('reportModal').classList.add('hidden');
    }
    window.submitReport = function () {
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
                    window.closeReportModal();
                    alert(data.message ||
                        'Report submitted successfully. Thank you for helping keep our community safe.');
                } else {
                    alert(data.message || 'Failed to submit report. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error submitting report:', error);
                alert('Failed to submit report. Please try again.');
            });
    }

    window.toggleEditComment = function (commentId) {
        const commentDiv = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!commentDiv) return;

        const textElement = commentDiv.querySelector('.comment-text');
        const editForm = commentDiv.querySelector('.comment-edit-form');
        const editBtn = commentDiv.querySelector('.edit-btn');

        textElement.classList.add('hidden');
        editForm.classList.remove('hidden');
        editBtn.classList.add('hidden');
    }

    window.cancelCommentEdit = function (commentId) {
        const commentDiv = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!commentDiv) return;

        const textElement = commentDiv.querySelector('.comment-text');
        const editForm = commentDiv.querySelector('.comment-edit-form');
        const editBtn = commentDiv.querySelector('.edit-btn');
        const textarea = commentDiv.querySelector('.edit-textarea');

        textarea.value = textElement.textContent;

        textElement.classList.remove('hidden');
        editForm.classList.add('hidden');
        editBtn.classList.remove('hidden');
    }

    window.saveCommentEdit = function (commentId) {
        const commentDiv = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (!commentDiv) return;

        const textarea = commentDiv.querySelector('.edit-textarea');
        const newContent = textarea.value.trim();

        if (!newContent) {
            alert('Comment cannot be empty.');
            return;
        }

        if (newContent.length > 1000) {
            alert('Comment must be 1000 characters or less.');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/comments/${commentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                content: newContent
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const textElement = commentDiv.querySelector('.comment-text');
                    const editForm = commentDiv.querySelector('.comment-edit-form');
                    const editBtn = commentDiv.querySelector('.edit-btn');

                    textElement.textContent = newContent;
                    textElement.classList.remove('hidden');
                    editForm.classList.add('hidden');
                    editBtn.classList.remove('hidden');
                } else {
                    alert(data.message || 'Failed to update comment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating comment:', error);
                alert('Failed to update comment. Please try again.');
            });
    }

    window.deleteComment = function (commentId) {
        if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const commentDiv = document.querySelector(`[data-comment-id="${commentId}"]`);
                    if (commentDiv) {
                        commentDiv.remove();
                    }

                    const commentElements = document.querySelectorAll('[data-comment-id]');
                    const actualCount = commentElements.length;

                    const modalCountElem = document.getElementById('commentsCount');
                    if (modalCountElem) {
                        modalCountElem.textContent = actualCount;
                    }

                    const feedCountElem = document.getElementById(`comment-count-${currentPostId}`);
                    if (feedCountElem) {
                        feedCountElem.textContent = actualCount;
                    }
                } else {
                    alert(data.message || 'Failed to delete comment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error deleting comment:', error);
                alert('Failed to delete comment. Please try again.');
            });
    }
</script>