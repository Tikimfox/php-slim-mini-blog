/**
 * Mini-Blog - Articles Management
 * Handles article listing and creation
 */

const API_BASE_URL = '/api';

// Load articles on page load
document.addEventListener('DOMContentLoaded', function() {
    loadArticles();
    setupCreateArticleForm();
});

/**
 * Load all articles from API
 */
async function loadArticles() {
    const container = document.getElementById('articles-container');

    try {
        console.log('Fetching articles from:', `${API_BASE_URL}/articles`);
        const response = await fetch(`${API_BASE_URL}/articles`);
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('Received data:', result);

        // Clear loading spinner
        container.innerHTML = '';

        if (result.success && result.data && result.data.length > 0) {
            // Display articles
            result.data.forEach(article => {
                container.innerHTML += createArticleCard(article);
            });
        } else {
            // No articles found
            container.innerHTML = `
                <div class="col-12 text-center">
                    <p class="text-muted">No articles yet. Be the first to create one!</p>
                </div>
            `;
        }

    } catch (error) {
        console.error('Error loading articles:', error);
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> Failed to load articles. Please try again later.
                </div>
            </div>
        `;
    }
}

/**
 * Create HTML for article card
 */
function createArticleCard(article) {
    // Use description from API (already truncated to 200 chars by backend)
    const excerpt = article.description || article.content || '';

    // Format date
    const date = new Date(article.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });

    return `
        <div class="col-12">
            <article>
                <h2 class="title">
                    <a href="blog-details.html?id=${article.id}">${escapeHtml(article.title)}</a>
                </h2>

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
                    <p>${escapeHtml(excerpt)}</p>
                    <div class="read-more">
                        <a href="blog-details.html?id=${article.id}">Read More</a>
                    </div>
                </div>
            </article>
        </div>
    `;
}

/**
 * Setup create article form submission
 */
function setupCreateArticleForm() {
    const form = document.getElementById('create-article-form');
    const messageDiv = document.getElementById('create-article-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const title = document.getElementById('article-title').value.trim();
        const author = document.getElementById('article-author').value.trim();
        const content = document.getElementById('article-content').value.trim();

        // Validate
        if (!title || !author || !content) {
            showMessage(messageDiv, 'Please fill in all fields.', 'danger');
            return;
        }

        // Disable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Publishing...';

        try {
            const response = await fetch(`${API_BASE_URL}/articles`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title: title,
                    author: author,
                    content: content
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Success
                showMessage(messageDiv, 'Article published successfully!', 'success');

                // Clear form
                form.reset();

                // Reload articles
                loadArticles();

                // Scroll to articles
                setTimeout(() => {
                    document.getElementById('blog-posts').scrollIntoView({
                        behavior: 'smooth'
                    });
                }, 1000);

            } else {
                // Error from API
                showMessage(messageDiv, result.message || 'Failed to create article.', 'danger');
            }

        } catch (error) {
            console.error('Error creating article:', error);
            showMessage(messageDiv, 'Network error. Please try again.', 'danger');
        } finally {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
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

