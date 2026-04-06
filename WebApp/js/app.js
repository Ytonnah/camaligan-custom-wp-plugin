// Get WordPress REST API base URL
// Adjust the domain below to match your WordPress installation
const API_BASE = 'http://localhost/wordpress/wp-json/wp/v2/';

// Switch between tabs
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all nav tabs
    document.querySelectorAll('.nav-tab').forEach(nav => {
        nav.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName).classList.add('active');

    // Add active class to clicked nav
    event.target.classList.add('active');

    // Load data for the tab
    if (tabName === 'news') {
        loadNews();
    } else if (tabName === 'bac') {
        loadBAC();
    } else if (tabName === 'posts') {
        loadPosts();
    } else if (tabName === 'dashboard') {
        loadDashboard();
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            switchTab(this.dataset.tab);
        });
    });

    // Search functionality
    document.getElementById('news-search')?.addEventListener('input', debounce(searchNews, 300));
    document.getElementById('bac-search')?.addEventListener('input', debounce(searchBAC, 300));

    // Load initial dashboard
    loadDashboard();
});

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load Dashboard
async function loadDashboard() {
    try {
        const [newsCount, bacCount, postsCount] = await Promise.all([
            getCount('news_item'),
            getCount('bac_item'),
            getCount('post')
        ]);

        document.getElementById('news-count').textContent = newsCount;
        document.getElementById('bac-count').textContent = bacCount;
        document.getElementById('posts-count').textContent = postsCount;

        // Load recent news
        const recentNews = await fetch(`${API_BASE}news_item?per_page=3`).then(r => r.json());
        displayRecentNews(recentNews);

        // Load recent posts
        const recentPosts = await fetch(`${API_BASE}posts?per_page=3`).then(r => r.json());
        displayRecentPosts(recentPosts);
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

// Get count of items
async function getCount(postType) {
    try {
        const endpoint = postType === 'post' ? 'posts' : postType;
        const response = await fetch(`${API_BASE}${endpoint}?per_page=1`);
        const data = await response.json();
        return response.headers.get('x-wp-totalpages') || 0;
    } catch (error) {
        console.error('Error getting count:', error);
        return 0;
    }
}

// Load News
async function loadNews() {
    try {
        const response = await fetch(`${API_BASE}news_item?per_page=20`);
        const news = await response.json();
        displayNews(news);
    } catch (error) {
        console.error('Error loading news:', error);
        document.getElementById('news-list').innerHTML = '<div class="empty-state"><p>Error loading news items</p></div>';
    }
}

// Display News
function displayNews(news) {
    const container = document.getElementById('news-list');
    
    if (!news || news.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>📭 No news items found</p></div>';
        return;
    }

    container.innerHTML = news.map(item => {
        const title = item.title?.rendered || item.title || 'Untitled';
        const excerpt = item.excerpt?.rendered ? item.excerpt.rendered.replace(/<[^>]*>/g, '').substring(0, 150) : 'No description available';
        const category = item.news_category || 'General';
        
        return `
            <div class="item-card" onclick="showNewsDetail(${item.id})">
                <div class="item-header">
                    <h3 class="item-title">${title}</h3>
                </div>
                <div class="item-meta">
                    <span>📅 ${new Date(item.date).toLocaleDateString()}</span>
                    <span class="item-badge">${category}</span>
                </div>
                <div class="item-excerpt">${excerpt}...</div>
                <div class="item-actions">
                    <button class="btn btn-primary" onclick="event.stopPropagation(); showNewsDetail(${item.id})">Read More</button>
                </div>
            </div>
        `;
    }).join('');
}

// Display Recent News
function displayRecentNews(news) {
    const container = document.getElementById('recent-news');
    
    if (!news || news.length === 0) {
        container.innerHTML = '<p>No recent news</p>';
        return;
    }

    container.innerHTML = news.map(item => `
        <div class="item-card" style="margin-bottom: 10px;" onclick="switchTab('news')">
            <h4 style="margin: 0 0 5px 0;">${item.title.rendered}</h4>
            <small style="color: #999;">📅 ${new Date(item.date).toLocaleDateString()}</small>
        </div>
    `).join('');
}

// Search News
async function searchNews() {
    const searchTerm = document.getElementById('news-search').value;
    if (!searchTerm) {
        loadNews();
        return;
    }

    try {
        const response = await fetch(`${API_BASE}news_item?search=${encodeURIComponent(searchTerm)}`);
        const news = await response.json();
        displayNews(news);
    } catch (error) {
        console.error('Error searching news:', error);
    }
}

// Show News Detail
async function showNewsDetail(newsId) {
    try {
        const response = await fetch(`${API_BASE}news_item/${newsId}`);
        const item = await response.json();
        
        const content = `
            <h2>${item.title.rendered}</h2>
            <div class="item-meta">
                <span>📅 ${new Date(item.date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</span>
                ${item.news_category ? `<span class="item-badge">${item.news_category}</span>` : ''}
            </div>
            ${item.featured_media ? `<img src="${await getImageUrl(item.featured_media)}" class="item-image">` : ''}
            <div>${item.content.rendered}</div>
        `;
        
        document.getElementById('modal-body').innerHTML = content;
        document.getElementById('detail-modal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading news detail:', error);
    }
}

// Load BAC
async function loadBAC() {
    try {
        const response = await fetch(`${API_BASE}bac_item?per_page=20`);
        const bac = await response.json();
        displayBAC(bac);
    } catch (error) {
        console.error('Error loading BAC:', error);
        document.getElementById('bac-list').innerHTML = '<div class="empty-state"><p>Error loading BAC documents</p></div>';
    }
}

// Display BAC
function displayBAC(bac) {
    const container = document.getElementById('bac-list');
    
    if (!bac || bac.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>📭 No BAC documents found</p></div>';
        return;
    }

    container.innerHTML = bac.map(item => {
        const pdfUrl = item.bac_pdf_id ? `/wordpress/index.php?attachment_id=${item.bac_pdf_id}` : '#';
        return `
            <div class="item-card">
                <div class="item-header">
                    <h3 class="item-title">${item.title.rendered}</h3>
                </div>
                <div class="item-meta">
                    <span>📅 ${new Date(item.date).toLocaleDateString()}</span>
                </div>
                <div class="item-actions">
                    <a href="${pdfUrl}" class="btn btn-primary" download>Download PDF</a>
                </div>
            </div>
        `;
    }).join('');
}

// Search BAC
async function searchBAC() {
    const searchTerm = document.getElementById('bac-search').value;
    if (!searchTerm) {
        loadBAC();
        return;
    }

    try {
        const response = await fetch(`${API_BASE}bac_item?search=${encodeURIComponent(searchTerm)}`);
        const bac = await response.json();
        displayBAC(bac);
    } catch (error) {
        console.error('Error searching BAC:', error);
    }
}

// Load Posts
async function loadPosts() {
    try {
        const response = await fetch(`${API_BASE}posts?per_page=20`);
        const posts = await response.json();
        displayPosts(posts);
    } catch (error) {
        console.error('Error loading posts:', error);
        document.getElementById('posts-list').innerHTML = '<div class="empty-state"><p>Error loading posts</p></div>';
    }
}

// Display Posts
async function displayPosts(posts) {
    const container = document.getElementById('posts-list');
    
    if (!posts || posts.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>📭 No posts found. Create one!</p></div>';
        return;
    }

    const postsWithImages = await Promise.all(posts.map(async (post) => {
        let imageUrl = '';
        if (post.featured_media) {
            imageUrl = await getImageUrl(post.featured_media);
        }
        return { ...post, imageUrl };
    }));

    container.innerHTML = postsWithImages.map(item => {
        const title = item.title?.rendered || item.title || 'Untitled';
        const excerpt = item.excerpt?.rendered ? item.excerpt.rendered.replace(/<[^>]*>/g, '').substring(0, 150) : 'No description available';
        
        return `
            <div class="item-card" onclick="showPostDetail(${item.id})">
                <div class="item-header">
                    <h3 class="item-title">${title}</h3>
                </div>
                <div class="item-meta">
                    <span>📅 ${new Date(item.date).toLocaleDateString()}</span>
                </div>
                ${item.imageUrl ? `<img src="${item.imageUrl}" class="item-image">` : ''}
                <div class="item-excerpt">${excerpt}...</div>
                <div class="item-actions">
                    <button class="btn btn-primary" onclick="event.stopPropagation(); showPostDetail(${item.id})">Read More</button>
                </div>
            </div>
        `;
    }).join('');
}

// Display Recent Posts
async function displayRecentPosts(posts) {
    const container = document.getElementById('recent-posts');
    
    if (!posts || posts.length === 0) {
        container.innerHTML = '<p>No recent posts</p>';
        return;
    }

    container.innerHTML = posts.map(item => `
        <div class="item-card" style="margin-bottom: 10px;" onclick="switchTab('posts')">
            <h4 style="margin: 0 0 5px 0;">${item.title.rendered}</h4>
            <small style="color: #999;">📅 ${new Date(item.date).toLocaleDateString()}</small>
        </div>
    `).join('');
}

// Show Post Detail
async function showPostDetail(postId) {
    try {
        const response = await fetch(`${API_BASE}posts/${postId}`);
        const item = await response.json();
        
        let imageHtml = '';
        if (item.featured_media) {
            const imageUrl = await getImageUrl(item.featured_media);
            imageHtml = `<img src="${imageUrl}" class="item-image">`;
        }
        
        const content = `
            <h2>${item.title.rendered}</h2>
            <div class="item-meta">
                <span>📅 ${new Date(item.date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</span>
            </div>
            ${imageHtml}
            <div>${item.content.rendered}</div>
        `;
        
        document.getElementById('modal-body').innerHTML = content;
        document.getElementById('detail-modal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading post detail:', error);
    }
}

// Get Image URL
async function getImageUrl(mediaId) {
    try {
        const response = await fetch(`${API_BASE}media/${mediaId}`);
        const media = await response.json();
        return media.source_url;
    } catch (error) {
        console.error('Error getting image URL:', error);
        return '';
    }
}

// Show Post Form
function showPostForm() {
    document.getElementById('post-form').style.display = 'block';
}

// Hide Post Form
function hidePostForm() {
    document.getElementById('post-form').style.display = 'none';
    document.getElementById('post-title').value = '';
    document.getElementById('post-content').value = '';
    document.getElementById('post-image').value = '';
    document.getElementById('image-preview').innerHTML = '';
}

// Handle Post Image Preview
document.getElementById('post-image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
});

// Handle Post Submit
async function handlePostSubmit(event) {
    event.preventDefault();

    const title = document.getElementById('post-title').value;
    const content = document.getElementById('post-content').value;
    const imageFile = document.getElementById('post-image').files[0];
    const category = document.getElementById('post-category').value;

    if (!title || !content) {
        alert('Please fill in title and content');
        return;
    }

    try {
        // Create post
        const postData = {
            title: title,
            content: content,
            status: 'publish'
        };

        const postResponse = await fetch(`${API_BASE}posts`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(postData)
        });

        if (!postResponse.ok) {
            throw new Error('Failed to create post');
        }

        const post = await postResponse.json();

        // Upload image if provided
        if (imageFile) {
            const formData = new FormData();
            formData.append('file', imageFile);

            const mediaResponse = await fetch(`${API_BASE}media`, {
                method: 'POST',
                body: formData
            });

            if (mediaResponse.ok) {
                const media = await mediaResponse.json();
                
                // Set featured image
                await fetch(`${API_BASE}posts/${post.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        featured_media: media.id
                    })
                });
            }
        }

        alert('✅ Post published successfully!');
        hidePostForm();
        loadPosts();
    } catch (error) {
        console.error('Error creating post:', error);
        alert('❌ Error creating post: ' + error.message);
    }
}

// Close Modal
function closeModal() {
    document.getElementById('detail-modal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('detail-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
