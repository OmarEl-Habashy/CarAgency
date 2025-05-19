document.addEventListener('DOMContentLoaded', function() {
    const createPostForm = document.getElementById('createPostForm');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const caption = createPostForm.caption.value.trim();
            if (!caption) return;
            
            fetch('../app/controller/create_post_controller.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'caption=' + encodeURIComponent(caption)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const postHtml = `
                        <div class="post" id="post-${data.post_id}">
                            <div class="post-header">
                                <div class="post-user-avatar">${data.username.charAt(0).toUpperCase()}</div>
                                <div class="post-username">${data.username}</div>
                                <div class="post-date">${data.created_at}</div>
                            </div>
                            <div class="post-content">${data.caption.replace(/\n/g, '<br>')}</div>
                            ${data.content_url ? `<img class="post-image" src="${data.content_url}" alt="Post image">` : ''}
                            <button class="like-btn" data-post-id="${data.post_id}">
                                <span class="like-icon">👍</span>
                                <span class="like-count" id="like-count-${data.post_id}">0</span>
                            </button>
                            <button class="comments-btn" onclick="openCommentsModal(${data.post_id})">Comments</button>
                        </div>
                    `;
                    
                    const postsContainer = document.getElementById('postsContainer');
                    
                    const noPostsMsg = postsContainer.querySelector('div[style*="color:#2563eb"]');
                    if (noPostsMsg) {
                        postsContainer.removeChild(noPostsMsg);
                    }
                    
                    postsContainer.insertAdjacentHTML('afterbegin', postHtml);
                    
                    createPostForm.reset();
                    
                    const newLikeBtn = document.querySelector(`.like-btn[data-post-id="${data.post_id}"]`);
                    if (newLikeBtn) {
                        newLikeBtn.addEventListener('click', function() {
                            const postId = this.getAttribute('data-post-id');
                            fetch('../app/controller/like_post.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                body: 'post_id=' + postId
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    const likeBtn = document.querySelector('.like-btn[data-post-id="' + postId + '"]');
                                    const likeCount = document.getElementById('like-count-' + postId);
                                    if (data.liked) {
                                        likeBtn.classList.add('liked');
                                    } else {
                                        likeBtn.classList.remove('liked');
                                    }
                                    likeCount.textContent = data.like_count;
                                }
                            });
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error creating post:', error);
            });
        });
    }
    
    // Add event listeners to all like buttons
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            fetch('../app/controller/like_post.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'post_id=' + postId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const likeCount = this.querySelector('.like-count');
                    if (data.liked) {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                    likeCount.textContent = data.like_count;
                }
            })
            .catch(error => {
                console.error('Error liking post:', error);
            });
        });
    });
    
    // Set up delete confirmation modal
    const deleteModal = document.getElementById('deleteConfirmModal');
    if (deleteModal) {
        // When the modal is about to be shown, set the post ID
        deleteModal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract post ID from data-post-id attribute
            const postId = button.getAttribute('data-post-id');
            // Update the form's hidden input with the post ID
            document.getElementById('deletePostId').value = postId;
        });
    }
    
    // Fix the like button functionality
    document.querySelectorAll('.btn-outline-primary').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            fetch('../app/controller/like_post.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'post_id=' + postId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const likeBtn = document.querySelector('.btn-outline-primary[data-post-id="' + postId + '"]');
                    const likeIcon = likeBtn.querySelector('i');
                    const likeCount = document.getElementById('like-count-' + postId);
                    
                    if (data.liked) {
                        likeBtn.classList.add('active');
                        likeIcon.className = 'bi bi-hand-thumbs-up-fill';
                    } else {
                        likeBtn.classList.remove('active');
                        likeIcon.className = 'bi bi-hand-thumbs-up';
                    }
                    
                    likeCount.textContent = data.like_count;
                }
            });
        });
    });
    
    // Set up comments functionality
    const commentsModal = document.getElementById('commentsModal');
    const closeModal = document.getElementById('closeModal');
    
    if (closeModal && commentsModal) {
        closeModal.onclick = function() {
            commentsModal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == commentsModal) {
                commentsModal.style.display = 'none';
            }
        }
    }
    
    // Add media file preview functionality
    const mediaInput = document.getElementById('media-file');
    const mediaPreview = document.getElementById('media-preview');
    
    if (mediaInput && mediaPreview) {
        mediaInput.addEventListener('change', function() {
            // Clear previous preview
            mediaPreview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileReader = new FileReader();
                
                fileReader.onload = function(e) {
                    // Check if file is an image
                    if (file.type.match('image.*')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'media-preview-image';
                        mediaPreview.appendChild(img);
                    } 
                    // Check if file is a video
                    else if (file.type.match('video.*')) {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.className = 'media-preview-video';
                        video.controls = true;
                        mediaPreview.appendChild(video);
                    }
                    
                    // Add remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = 'Remove';
                    removeBtn.className = 'media-remove-btn';
                    removeBtn.onclick = function(e) {
                        e.preventDefault();
                        mediaInput.value = '';
                        mediaPreview.innerHTML = '';
                    };
                    mediaPreview.appendChild(removeBtn);
                };
                
                fileReader.readAsDataURL(file);
            }
        });
    }
});

function openCommentsModal(postId) {
    fetch('../app/controller/get_comments.php?post_id=' + postId)
    .then(response => response.text())
    .then(html => {
        document.getElementById('commentsBody').innerHTML = html;
        document.getElementById('commentsModal').style.display = 'block';
        attachCommentFormHandler(postId);
    })
    .catch(error => {
        console.error('Error fetching comments:', error);
    });
}

function attachCommentFormHandler(postId) {
    const form = document.getElementById('commentForm');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            const commentText = form.comment.value.trim();
            if (!commentText) return;
            
            fetch('../app/controller/add_comment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'post_id=' + postId + '&comment=' + encodeURIComponent(commentText)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const commentsList = document.getElementById('commentsList');
                    
                    // Check if there's a "no comments" message and remove it
                    const noComments = commentsList.querySelector('.no-comments');
                    if (noComments) {
                        commentsList.removeChild(noComments);
                    }
                    
                    // Create new comment with X-style formatting
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <div class="comment-avatar">${data.username.charAt(0).toUpperCase()}</div>
                        <div class="comment-content">
                            <div class="comment-username">${data.username}</div>
                            <div class="comment-text">${data.comment}</div>
                        </div>
                    `;
                    
                    commentsList.appendChild(newComment);
                    form.reset();
                    
                    // Update comment count in the interface
                    const commentCountEl = document.querySelector('.comments-count span');
                    if (commentCountEl) {
                        const currentCount = parseInt(commentCountEl.textContent);
                        commentCountEl.textContent = currentCount + 1;
                    }
                }
            })
            .catch(error => {
                console.error('Error posting comment:', error);
            });
        };
    }
}
