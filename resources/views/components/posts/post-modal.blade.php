<!-- Modal -->
<div id="postModal" class="fixed inset-0 z-50 hidden" onclick="closePostModal()">
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <!-- Modal panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-[90vw] h-[85vh] flex flex-col sm:flex-row"
                onclick="event.stopPropagation()">

                <!-- Left Side: Post Content -->
                <div class="flex-1 flex flex-col sm:border-r border-gray-200 overflow-hidden bg-white">
                    <!-- Header -->
                    <div class="p-8 flex justify-between items-center shrink-0">
                        <div id="modalAuthor" class="flex items-center gap-3">
                            <!-- js will inject author info here -->
                        </div>
                        @auth
                            <button id="modalEditButton"
                                class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100 hidden">
                                <i class="fas fa-edit text-3xl"></i>
                            </button>
                        @endauth
                    </div>

                    <!-- Content -->
                    <div id="modalContent" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                        <!-- JS will inject content here -->
                    </div>

                    <!-- Actions Footer -->
                    <div id="postActions"
                        class="p-8 pt-4 pb-6 border-t border-gray-200 flex items-center gap-6 shrink-0">
                        <!-- Like Button -->
                        <button onclick="likePost(event)"
                            class="group flex items-center gap-2 text-gray-600 hover:text-red-500 transition-colors">
                            <i class="far fa-heart text-4xl group-hover:scale-110 transition-transform"
                                id="modalLikeIcon"></i>
                            <span id="likesCount" class="text-2xl font-medium">0</span>
                        </button>

                        <!-- Comment Indicator -->
                        <div class="flex items-center gap-2 text-gray-600">
                            <i class="far fa-comment text-4xl"></i>
                            <span id="commentsCount" class="text-2xl font-medium">0</span>
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
                <div class="w-full sm:w-[500px] flex flex-col bg-gray-50 h-full border-l border-gray-200">
                    <!-- Header with Close -->
                    <div
                        class="p-4 border-b border-gray-200 flex justify-between items-center shrink-0 bg-white shadow-sm z-10">
                        <h3 class="font-semibold text-gray-900 text-4xl">Comments</h3>
                        <button onclick="closePostModal()"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Comments List -->
                    <div id="commentsSection" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                        @isset($comments)
                            <x-posts.comments-list :comments="$comments" />
                        @endisset
                    </div>

                    <!-- Add Comment Input -->
                    <div id="addCommentSection" class="p-4 border-t border-gray-200 bg-white shrink-0">
                        <div class="flex gap-3 items-center">
                            <div class="w-8 h-8 bg-gray-200 rounded-full shrink-0"></div>
                            <div class="flex-1 relative">
                                <input type="text" id="commentInput"
                                    class="w-full rounded-full border-gray-300 bg-gray-50 pl-4 pr-12 py-2.5 focus:border-[#38157a] focus:ring-[#38157a] focus:bg-white transition-all"
                                    placeholder="Write a comment..." onkeypress="handleCommentKeyPress(event)">
                                <button onclick="submitComment()"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-[#38157a] hover:text-[#5a3d8a] p-1.5 rounded-full hover:bg-purple-50 transition-colors">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="fixed inset-0 z-50 hidden" onclick="closeReportModal()">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-6">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-2xl p-6"
                onclick="event.stopPropagation()">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-4xl font-bold text-gray-900">Report Post</h2>
                    <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <p class="text-gray-600 mb-4 font-medium">Please provide a reason for reporting this post. Our team will
                    review it.</p>

                <textarea id="reportReason" placeholder="Describe why you're reporting this post (minimum 10 characters)..."
                    class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg focus:border-[#38157a] focus:ring-[#38157a] mb-6 resize-none"
                    maxlength="1000"></textarea>

                <div class="flex justify-end gap-3">
                    <x-ui.button onclick="closeReportModal()" variant="secondary">
                        Cancel
                        </x-button>
                        <x-ui.button onclick="submitReport()" variant="danger">
                            Submit Report
                            </x-button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPostId = null;
    window.openPostModal = function(post) {
        const modal = document.getElementById('postModal');
        const content = document.getElementById('modalContent');
        const authorDiv = document.getElementById('modalAuthor');
        const commentsSection = document.getElementById('commentsSection');
        const editBtn = document.getElementById('modalEditButton');
        const likeIcon = document.getElementById('modalLikeIcon');
        currentPostId = post.id;
        authorDiv.innerHTML = `
            <a href="/${post.username}" class="w-12 h-12 bg-gray-200 rounded-full shrink-0"></a>
            <div class="flex flex-col">
                <a href="/${post.username}" class="font-semibold text-black text-3xl hover:text-[#38157a] transition">${post.author_name}</a>
                <a href="/${post.username}" class="text-gray-600 text-xl">@${post.username}</a>
            </div>
        `;
        if (editBtn) {
            editBtn.style.display = 'none';
            if (window.isAuthenticated && window.currentUserUsername === post.username) {
                editBtn.style.display = 'block';
                editBtn.onclick = function() {
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
                starsHtml += '<i class="fas fa-star text-4xl"></i>';
            }
            html = `
            <div class="flex flex-col sm:flex-row gap-6">
                <div class="shrink-0 mx-auto sm:mx-0">
                    <img src="${post.media_poster}" 
                         class="rounded-xl shadow-lg object-cover ${post.media_type === 'music' ? 'w-96 h-96' : 'w-96 h-[36rem]'}" 
                         alt="${post.media_title}">
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-2">
                        <h3 class="text-6xl font-bold text-gray-900 leading-tight">${post.media_title}</h3>
                        <div class="text-yellow-400 shrink-0 mt-4">
                            ${starsHtml}
                        </div>
                    </div>
                    <p class="text-2xl text-gray-600 font-medium mb-1">${post.media_creator}</p>
                    <p class="text-xl text-gray-500 mb-6">${post.media_year}</p>
                    <p class="text-black">${post.content}</p>
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
                         class="w-full h-auto object-contain max-h-[500px] mx-auto">
                </div>
            `;
            }
            html += `<p class="text-black whitespace-pre-wrap">${post.content}</p>`;
        }
        if (post.created_at) {
            const date = new Date(post.created_at);
            html += `
            <div class="mt-8 pt-4 text-gray-600 text-xl flex items-center gap-2">
                <i class="far fa-clock"></i>
                ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} · ${date.toLocaleDateString()}
            </div>
        `;
        }
        content.innerHTML = html;
        document.getElementById('likesCount').textContent = post.likes_count || 0;
        document.getElementById('commentsCount').textContent = post.comments_count || 0;
        if (post.is_liked) {
            likeIcon.classList.remove('far');
            likeIcon.classList.add('fas', 'text-red-500');
        } else {
            likeIcon.classList.remove('fas', 'text-red-500');
            likeIcon.classList.add('far');
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
    }
    window.loadComments = function(postId) {
        const commentsSection = document.getElementById('commentsSection');
        commentsSection.innerHTML = `
        <div class="flex justify-center items-center py-8 text-gray-400">
            <i class="fas fa-circle-notch fa-spin text-2xl"></i>
        </div>
    `;
        fetch(`/posts/${postId}/comments`)
            .then(response => response.text())
            .then(html => {
                commentsSection.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading comments:', error);
                commentsSection.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <p>Error loading comments</p>
                <button onclick="window.loadComments(${postId})" class="text-sm underline mt-2">Try again</button>
            </div>
        `;
            });
    }
    window.closePostModal = function() {
        const modal = document.getElementById('postModal');
        modal.classList.add('hidden');
        currentPostId = null;
        document.getElementById('commentInput').value = '';
    }
    window.likePost = function(event) {
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
                    } else {
                        likeIcon.classList.remove('fas', 'text-red-500');
                        likeIcon.classList.add('far');
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
    window.handleCommentKeyPress = function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            window.submitComment();
        }
    }
    window.submitComment = function() {
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
                    const modalCountElem = document.getElementById('commentsCount');
                    modalCountElem.textContent = parseInt(modalCountElem.textContent) + 1;
                    const timelineCommentCount = document.querySelector(
                        `#post-${currentPostId} .comments-count`);
                    if (timelineCommentCount) {
                        timelineCommentCount.textContent = parseInt(timelineCommentCount.textContent) + 1;
                    }
                }
            })
            .catch(error => {
                console.error('Error submitting comment:', error);
                alert('Failed to post comment. Please try again.');
            });
    }
    window.openReportModal = function(event) {
        event.stopPropagation();
        if (!currentPostId) return;
        document.getElementById('reportModal').classList.remove('hidden');
        document.getElementById('reportReason').value = '';
    }
    window.closeReportModal = function() {
        document.getElementById('reportModal').classList.add('hidden');
    }
    window.submitReport = function() {
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
</script>
