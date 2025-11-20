<!-- Modal -->
<div id="postModal" style="display: none;"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closePostModal()">
  <div class="bg-white p-8 max-w-2xl w-full mx-4" onclick="event.stopPropagation()">
    <div id="modalContent"></div>
    <button onclick="closePostModal()" class="mt-4 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Close</button>
  </div>
</div>

<script>
  function openPostModal(post) {
    const modal = document.getElementById('postModal');
    const content = document.getElementById('modalContent');

    let html = '<div>';
    html += '<div class="mb-4">';
    html += '<strong>Author:</strong> ' + post.author_name + ' (@' + post.username + ')';
    html += '</div>';

    if (post.rating) {
      html += '<div class="mb-4">';
      html += '<strong>Rating:</strong> ⭐ ' + post.rating + '/5';
      html += '</div>';
      html += '<div class="mb-4">';
      html += '<strong>Media:</strong> ' + post.media_title;
      html += '</div>';
    }

    html += '<div class="mb-4">';
    html += '<strong>Content:</strong><br>' + post.content;
    html += '</div>';

    if (post.created_at) {
      html += '<div class="text-gray-500 text-sm">';
      html += 'Posted: ' + new Date(post.created_at).toLocaleString();
      html += '</div>';
    }

    html += '</div>';

    content.innerHTML = html;
    modal.style.display = 'flex';
  }

  function closePostModal() {
    const modal = document.getElementById('postModal');
    modal.style.display = 'none';
  }
</script>