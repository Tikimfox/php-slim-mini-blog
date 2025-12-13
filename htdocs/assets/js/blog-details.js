/**
 * Mini-Blog - Article Details with Comments & Replies
 * Handles single article display, comments listing, and reply functionality
 */

const API_BASE_URL = '/api';
let currentArticleId = null;

// Load article and comments on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get article ID from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    currentArticleId = urlParams.get('id');

    if (!currentArticleId) {
        showError('No article ID provided');
        return;
    }

    loadArticle(currentArticleId);
    loadComments(currentArticleId);
    setupCommentForm();
    setupReplyForm();
});

/**
 * Load single article from API
 */
async function loadArticle(articleId) {
    const loadingDiv = document.getElementById('article-loading');
    const articleContainer = document.getElementById('article-container');

    try {
        const response = await fetch(`${API_BASE_URL}/articles/${articleId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.data) {
            // Hide loading, show article
            loadingDiv.style.display = 'none';
            articleContainer.style.display = 'block';

            // Populate article content
            displayArticle(result.data);
        } else {
            throw new Error(result.message || 'Article not found');
        }

    } catch (error) {
        console.error('Error loading article:', error);
        loadingDiv.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <strong>Error!</strong> ${error.message}
                <br><a href="blog.html" class="btn btn-primary mt-3">Back to Articles</a>
            </div>
        `;
    }
}

/**
 * Display article content
 */
function displayArticle(article) {
    const container = document.getElementById('article-container');

    // Format date
    const date = new Date(article.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Update page title
    document.title = `${article.title} - Mini-Blog`;

    // Build article HTML
    container.innerHTML = `
        <h2 class="title">${escapeHtml(article.title)}</h2>

        <div class="meta-top">
            <ul>
                <li class="d-flex align-items-center">
                    <i class="bi bi-person"></i>
                    <span>${escapeHtml(article.author)}</span>
                </li>
                <li class="d-flex align-items-center">
                    <i class="bi bi-clock"></i>
                    <time datetime="${article.created_at}">${formattedDate}</time>
                </li>
            </ul>
        </div>

        <div class="content">
            ${formatContent(article.content)}
        </div>
    `;
}

/**
 * Format article content (convert line breaks to paragraphs)
 */
function formatContent(content) {
    const escaped = escapeHtml(content);
    const paragraphs = escaped.split('\n\n');
    return paragraphs.map(p => `<p>${p.replace(/\n/g, '<br>')}</p>`).join('');
}

/**
 * Load comments for article
 */
async function loadComments(articleId) {
    const commentsContainer = document.getElementById('comments-container');
    const commentsCount = document.getElementById('comments-count');

    try {
        const response = await fetch(`${API_BASE_URL}/articles/${articleId}/comments`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success && result.data) {
            const comments = result.data;
            console.log('Loaded comments:', comments);

            // Update count
            const count = comments.length;
            commentsCount.textContent = `${count} Comment${count !== 1 ? 's' : ''}`;

            if (count > 0) {
                // Display comments with hierarchical structure
                commentsContainer.innerHTML = buildCommentsTree(comments);
            } else {
                commentsContainer.innerHTML = '<p class="text-muted">No comments yet. Be the first to comment!</p>';
            }
        } else {
            commentsContainer.innerHTML = '<p class="text-muted">No comments yet. Be the first to comment!</p>';
        }

    } catch (error) {
        console.error('Error loading comments:', error);
        commentsContainer.innerHTML = `
            <div class="alert alert-warning" role="alert">
                Failed to load comments.
            </div>
        `;
    }
}

/**
 * Build hierarchical comments tree (parent comments with replies)
 */
function buildCommentsTree(comments) {
    // Backend already returns nested structure with 'replies' array
    let html = '';

    comments.forEach(comment => {
        // Add parent comment
        html += buildCommentHTML(comment, false);

        // Add replies if they exist
        if (comment.replies && comment.replies.length > 0) {
            comment.replies.forEach(reply => {
                html += buildCommentHTML(reply, true);
            });
        }
    });

    return html;
}

/**
 * Build single comment HTML
 */
function buildCommentHTML(comment, isReply) {
    const date = new Date(comment.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });

    const replyClass = isReply ? 'comment comment-reply' : 'comment';

    return `
        <div id="comment-${comment.id}" class="${replyClass}">
            <div class="d-flex">
                <div class="comment-img">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
                        ${comment.author.charAt(0).toUpperCase()}
                    </div>
                </div>
                <div>
                    <h5>
                        <span>${escapeHtml(comment.author)}</span>
                        ${!isReply ? `<a href="#" class="reply" onclick="showReplyForm(${comment.id}, '${escapeHtml(comment.author)}'); return false;">
                            <i class="bi bi-reply-fill"></i> Reply
                        </a>` : ''}
                    </h5>
                    <time datetime="${comment.created_at}">${formattedDate}</time>
                    <p>${escapeHtml(comment.content)}</p>
                </div>
            </div>
        </div>
    `;
}

/**
 * Setup comment form submission
 */
function setupCommentForm() {
    const form = document.getElementById('add-comment-form');
    const messageDiv = document.getElementById('comment-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const author = document.getElementById('comment-author').value.trim();
        const content = document.getElementById('comment-content').value.trim();

        if (!author || !content) {
            showMessage(messageDiv, 'Please fill in all fields.', 'danger');
            return;
        }

        // Disable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Posting...';

        try {
            const response = await fetch(`${API_BASE_URL}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    article_id: parseInt(currentArticleId),
                    author: author,
                    content: content
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Success
                showMessage(messageDiv, 'Comment posted successfully!', 'success');

                // Clear form
                form.reset();

                // Reload comments
                loadComments(currentArticleId);

            } else {
                showMessage(messageDiv, result.message || 'Failed to post comment.', 'danger');
            }

        } catch (error) {
            console.error('Error posting comment:', error);
            showMessage(messageDiv, 'Network error. Please try again.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

/**
 * Setup reply form submission
 */
function setupReplyForm() {
    const form = document.getElementById('add-reply-form');
    const messageDiv = document.getElementById('reply-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const author = document.getElementById('reply-author').value.trim();
        const content = document.getElementById('reply-content').value.trim();
        const parentId = document.getElementById('reply-parent-id').value;

        if (!author || !content || !parentId) {
            showMessage(messageDiv, 'Please fill in all fields.', 'danger');
            return;
        }

        // Disable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Posting...';

        try {
            const payload = {
                article_id: parseInt(currentArticleId),
                parent_id: parseInt(parentId),
                author: author,
                content: content
            };
            console.log('Posting reply:', payload);

            const response = await fetch(`${API_BASE_URL}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            console.log('Reply response:', result);

            if (response.ok && result.success) {
                // Success
                showMessage(messageDiv, 'Reply posted successfully!', 'success');

                // Clear and hide form
                setTimeout(() => {
                    cancelReply();
                    loadComments(currentArticleId);
                }, 1000);

            } else {
                showMessage(messageDiv, result.message || 'Failed to post reply.', 'danger');
            }

        } catch (error) {
            console.error('Error posting reply:', error);
            showMessage(messageDiv, 'Network error. Please try again.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

/**
 * Show reply form for a specific comment
 */
function showReplyForm(commentId, authorName) {
    const replyContainer = document.getElementById('reply-form-container');
    const replyToAuthor = document.getElementById('reply-to-author');
    const parentIdInput = document.getElementById('reply-parent-id');

    // Set parent comment ID
    parentIdInput.value = commentId;

    // Set author name being replied to
    replyToAuthor.textContent = authorName;

    // Show form
    replyContainer.style.display = 'block';

    // Scroll to form
    replyContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Focus on author input
    document.getElementById('reply-author').focus();
}

/**
 * Cancel reply and hide form
 */
function cancelReply() {
    const replyContainer = document.getElementById('reply-form-container');
    const form = document.getElementById('add-reply-form');
    const messageDiv = document.getElementById('reply-message');

    // Hide form
    replyContainer.style.display = 'none';

    // Clear form
    form.reset();

    // Clear messages
    messageDiv.innerHTML = '';
}

/**
 * Show error message
 */
function showError(message) {
    const loadingDiv = document.getElementById('article-loading');
    loadingDiv.innerHTML = `
        <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> ${message}
            <br><a href="blog.html" class="btn btn-primary mt-3">Back to Articles</a>
        </div>
    `;
}

/**
 * Show message to user
 */
function showMessage(element, message, type) {
    element.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        element.innerHTML = '';
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

