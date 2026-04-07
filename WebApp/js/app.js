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
    } else if (tabName === 'tourism') {
        loadTourism();
    } else if (tabName === 'beneficiaries') {
        loadBeneficiaries();
    } else if (tabName === 'media') {
        loadMedia();
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
    document.getElementById('tourism-search')?.addEventListener('input', debounce(searchTourism, 300));
    document.getElementById('beneficiaries-search')?.addEventListener('input', debounce(searchBeneficiaries, 300));
    document.getElementById('media-search')?.addEventListener('input', debounce(searchMedia, 300));

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
        const [newsCount, bacCount, postsCount, tourismCount, beneficiariesCount, mediaCount] = await Promise.all([
            getCount('news_item'),
            getCount('bac_item'),
            getCount('post'),
            getCount('tourism_item'),
            getCount('beneficiary_item'),
            getCount('media_gallery')
        ]);

        document.getElementById('news-count').textContent = newsCount;
        document.getElementById('bac-count').textContent = bacCount;
        document.getElementById('posts-count').textContent = postsCount;
        document.getElementById('tourism-count').textContent = tourismCount;
        document.getElementById('beneficiaries-count').textContent = beneficiariesCount;
        document.getElementById('media-count').textContent = mediaCount;

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

// Load Tourism
async function loadTourism() {
    try {
        const response = await fetch(`${API_BASE}tourism_item?per_page=20`);
        const tourism = await response.json();
        displayTourism(tourism);
    } catch (error) {
        console.error('Error loading tourism:', error);
        document.getElementById('tourism-list').innerHTML = '<div class="empty-state"><p>Error loading tourism destinations</p></div>';
    }
}

// Display Tourism
async function displayTourism(tourism) {
    const container = document.getElementById('tourism-list');
    
    if (!tourism || tourism.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>✈️ No tourism destinations found</p></div>';
        return;
    }

    const tourismWithImages = await Promise.all(tourism.map(async (item) => {
        let imageUrl = '';
        if (item.featured_media) {
            imageUrl = await getImageUrl(item.featured_media);
        }
        return { ...item, imageUrl };
    }));

    container.innerHTML = tourismWithImages.map(item => {
        const title = item.title?.rendered || 'Untitled';
        const excerpt = item.content?.rendered ? item.content.rendered.replace(/<[^>]*>/g, '').substring(0, 150) : 'No description';
        const type = item.tourism_type || 'Destination';
        const location = item.tourism_location || 'Location';
        const rating = item.tourism_rating || '—';
        
        return `
            <div class="item-card" onclick="showTourismDetail(${item.id})">
                <div class="item-header">
                    <h3 class="item-title">${title}</h3>
                </div>
                <div class="item-meta">
                    <span class="item-badge">${type}</span>
                    <span>📍 ${location}</span>
                    <span>⭐ ${rating}/5</span>
                </div>
                ${item.imageUrl ? `<img src="${item.imageUrl}" class="item-image" style="max-height: 200px;">` : ''}
                <div class="item-excerpt">${excerpt}...</div>
                <div class="item-actions">
                    <button class="btn btn-primary" onclick="event.stopPropagation(); showTourismDetail(${item.id})">View Details</button>
                </div>
            </div>
        `;
    }).join('');
}

// Search Tourism
async function searchTourism() {
    const searchTerm = document.getElementById('tourism-search').value;
    if (!searchTerm) {
        loadTourism();
        return;
    }

    try {
        const response = await fetch(`${API_BASE}tourism_item?search=${encodeURIComponent(searchTerm)}`);
        const tourism = await response.json();
        displayTourism(tourism);
    } catch (error) {
        console.error('Error searching tourism:', error);
    }
}

// Show Tourism Detail
async function showTourismDetail(tourismId) {
    try {
        const response = await fetch(`${API_BASE}tourism_item/${tourismId}`);
        const item = await response.json();
        
        let imageHtml = '';
        if (item.featured_media) {
            const imageUrl = await getImageUrl(item.featured_media);
            imageHtml = `<img src="${imageUrl}" class="item-image">`;
        }
        
        const type = item.tourism_type ? `<span class="item-badge">${item.tourism_type}</span>` : '';
        const location = item.tourism_location ? `<p><strong>📍 Location:</strong> ${item.tourism_location}</p>` : '';
        const rating = item.tourism_rating ? `<p><strong>⭐ Rating:</strong> ${item.tourism_rating}/5</p>` : '';
        
        const content = `
            <h2>${item.title.rendered}</h2>
            <div class="item-meta">
                ${type}
                ${location}
                ${rating}
            </div>
            ${imageHtml}
            <div>${item.content.rendered}</div>
        `;
        
        document.getElementById('modal-body').innerHTML = content;
        document.getElementById('detail-modal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading tourism detail:', error);
    }
}

// Show Tourism Form
function showTourismForm() {
    document.getElementById('tourism-form').style.display = 'block';
}

// Hide Tourism Form
function hideTourismForm() {
    document.getElementById('tourism-form').style.display = 'none';
    document.getElementById('tourism-title').value = '';
    document.getElementById('tourism-description').value = '';
    document.getElementById('tourism-type').value = '';
    document.getElementById('tourism-location').value = '';
    document.getElementById('tourism-rating').value = '';
    document.getElementById('tourism-image').value = '';
    document.getElementById('tourism-image-preview').innerHTML = '';
    document.getElementById('tourism-featured').checked = false;
}

// Handle Tourism Image Preview
document.getElementById('tourism-image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('tourism-image-preview');
            preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
});

// Handle Tourism Submit
async function handleTourismSubmit(event) {
    event.preventDefault();

    const title = document.getElementById('tourism-title').value;
    const description = document.getElementById('tourism-description').value;
    const type = document.getElementById('tourism-type').value;
    const location = document.getElementById('tourism-location').value;
    const rating = document.getElementById('tourism-rating').value;
    const imageFile = document.getElementById('tourism-image').files[0];
    const featured = document.getElementById('tourism-featured').checked;

    if (!title || !description) {
        alert('Please fill in title and description');
        return;
    }

    try {
        // Create tourism post
        const tourismData = {
            title: title,
            content: description,
            status: 'publish',
            meta: {
                tourism_type: type,
                tourism_location: location,
                tourism_rating: rating ? parseInt(rating) : 0,
                tourism_featured: featured ? 1 : 0
            }
        };

        const tourismResponse = await fetch(`${API_BASE}tourism_item`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(tourismData)
        });

        if (!tourismResponse.ok) {
            throw new Error('Failed to create tourism destination');
        }

        const tourism = await tourismResponse.json();

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
                await fetch(`${API_BASE}tourism_item/${tourism.id}`, {
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

        alert('✅ Tourism destination added successfully!');
        hideTourismForm();
        loadTourism();
    } catch (error) {
        console.error('Error creating tourism destination:', error);
        alert('❌ Error creating destination: ' + error.message);
    }
}

// ============================================
// BENEFICIARIES FUNCTIONS
// ============================================

// Load Beneficiaries
async function loadBeneficiaries() {
    try {
        const response = await fetch(`${API_BASE}beneficiary_item?per_page=20`);
        const beneficiaries = await response.json();
        displayBeneficiaries(beneficiaries);
    } catch (error) {
        console.error('Error loading beneficiaries:', error);
        document.getElementById('beneficiaries-list').innerHTML = '<div class="empty-state"><p>Error loading beneficiaries</p></div>';
    }
}

// Display Beneficiaries
async function displayBeneficiaries(beneficiaries) {
    const container = document.getElementById('beneficiaries-list');
    
    if (!beneficiaries || beneficiaries.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>👥 No beneficiaries found. Add one!</p></div>';
        return;
    }

    const beneficiariesWithImages = await Promise.all(beneficiaries.map(async (item) => {
        let imageUrl = '';
        if (item.featured_media) {
            imageUrl = await getImageUrl(item.featured_media);
        }
        return { ...item, imageUrl };
    }));

    container.innerHTML = beneficiariesWithImages.map(item => {
        const title = item.title?.rendered || 'Untitled';
        const excerpt = item.content?.rendered ? item.content.rendered.replace(/<[^>]*>/g, '').substring(0, 100) : 'No description';
        const type = item.beneficiary_type || 'Individual';
        const barangay = item.beneficiary_barangay || 'Unknown';
        const status = item.beneficiary_status || 'Pending';
        const program = item.beneficiary_program || 'General';
        
        const statusColor = status === 'Active' ? '#4CAF50' : status === 'Inactive' ? '#f44336' : '#FF9800';
        
        return `
            <div class="item-card" onclick="showBeneficiaryDetail(${item.id})">
                <div class="item-header">
                    <h3 class="item-title">${title}</h3>
                </div>
                <div class="item-meta">
                    <span class="item-badge" style="background-color: #2196F3;">${type}</span>
                    <span>📍 ${barangay}</span>
                    <span style="background-color: ${statusColor}; padding: 2px 8px; border-radius: 20px; color: white; font-size: 12px;">${status}</span>
                </div>
                ${item.imageUrl ? `<img src="${item.imageUrl}" class="item-image" style="max-height: 200px;">` : ''}
                <div class="item-excerpt">${excerpt}...</div>
                <div class="item-meta">
                    <span>📌 Program: ${program}</span>
                </div>
                <div class="item-actions">
                    <button class="btn btn-primary" onclick="event.stopPropagation(); showBeneficiaryDetail(${item.id})">View Details</button>
                </div>
            </div>
        `;
    }).join('');
}

// Search Beneficiaries
async function searchBeneficiaries() {
    const searchTerm = document.getElementById('beneficiaries-search').value;
    if (!searchTerm) {
        loadBeneficiaries();
        return;
    }

    try {
        const response = await fetch(`${API_BASE}beneficiary_item?search=${encodeURIComponent(searchTerm)}`);
        const beneficiaries = await response.json();
        displayBeneficiaries(beneficiaries);
    } catch (error) {
        console.error('Error searching beneficiaries:', error);
    }
}

// Show Beneficiary Detail
async function showBeneficiaryDetail(beneficiaryId) {
    try {
        const response = await fetch(`${API_BASE}beneficiary_item/${beneficiaryId}`);
        const item = await response.json();
        
        let imageHtml = '';
        if (item.featured_media) {
            const imageUrl = await getImageUrl(item.featured_media);
            imageHtml = `<img src="${imageUrl}" class="item-image">`;
        }
        
        const type = item.beneficiary_type ? `<span class="item-badge">${item.beneficiary_type}</span>` : '';
        const barangay = item.beneficiary_barangay ? `<p><strong>📍 Barangay:</strong> ${item.beneficiary_barangay}</p>` : '';
        const contact = item.beneficiary_contact ? `<p><strong>📞 Contact:</strong> ${item.beneficiary_contact}</p>` : '';
        const program = item.beneficiary_program ? `<p><strong>📌 Program:</strong> ${item.beneficiary_program}</p>` : '';
        const date = item.beneficiary_date ? `<p><strong>📅 Date Registered:</strong> ${new Date(item.beneficiary_date).toLocaleDateString()}</p>` : '';
        const status = item.beneficiary_status ? `<p><strong>Status:</strong> <span style="padding: 4px 8px; border-radius: 4px; background-color: ${item.beneficiary_status === 'Active' ? '#4CAF50' : item.beneficiary_status === 'Inactive' ? '#f44336' : '#FF9800'}; color: white;">${item.beneficiary_status}</span></p>` : '';
        
        const content = `
            <h2>${item.title.rendered}</h2>
            <div class="item-meta">
                ${type}
                ${status}
            </div>
            ${imageHtml}
            <div>${item.content?.rendered || 'No description available'}</div>
            ${barangay}
            ${contact}
            ${program}
            ${date}
        `;
        
        document.getElementById('modal-body').innerHTML = content;
        document.getElementById('detail-modal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading beneficiary detail:', error);
    }
}

// Show Beneficiary Form
function showBeneficiaryForm() {
    document.getElementById('beneficiary-form').style.display = 'block';
}

// Hide Beneficiary Form
function hideBeneficiaryForm() {
    document.getElementById('beneficiary-form').style.display = 'none';
    document.getElementById('beneficiary-name').value = '';
    document.getElementById('beneficiary-description').value = '';
    document.getElementById('beneficiary-barangay').value = '';
    document.getElementById('beneficiary-type').value = 'Individual';
    document.getElementById('beneficiary-contact').value = '';
    document.getElementById('beneficiary-program').value = '';
    document.getElementById('beneficiary-date').value = '';
    document.getElementById('beneficiary-status').value = 'Pending';
    document.getElementById('beneficiary-image').value = '';
    document.getElementById('beneficiary-image-preview').innerHTML = '';
}

// Handle Beneficiary Image Preview
document.getElementById('beneficiary-image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('beneficiary-image-preview');
            preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
});

// Handle Beneficiary Submit
async function handleBeneficiarySubmit(event) {
    event.preventDefault();

    const name = document.getElementById('beneficiary-name').value;
    const description = document.getElementById('beneficiary-description').value;
    const barangay = document.getElementById('beneficiary-barangay').value;
    const type = document.getElementById('beneficiary-type').value;
    const contact = document.getElementById('beneficiary-contact').value;
    const program = document.getElementById('beneficiary-program').value;
    const date = document.getElementById('beneficiary-date').value;
    const status = document.getElementById('beneficiary-status').value;
    const imageFile = document.getElementById('beneficiary-image').files[0];

    if (!name || !description || !barangay || !program || !date) {
        alert('Please fill in all required fields');
        return;
    }

    try {
        // Create beneficiary post
        const beneficiaryData = {
            title: name,
            content: description,
            status: 'publish',
            meta: {
                beneficiary_type: type,
                beneficiary_barangay: barangay,
                beneficiary_contact: contact,
                beneficiary_program: program,
                beneficiary_date: date,
                beneficiary_status: status
            }
        };

        const beneficiaryResponse = await fetch(`${API_BASE}beneficiary_item`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(beneficiaryData)
        });

        if (!beneficiaryResponse.ok) {
            throw new Error('Failed to create beneficiary');
        }

        const beneficiary = await beneficiaryResponse.json();

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
                await fetch(`${API_BASE}beneficiary_item/${beneficiary.id}`, {
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

        alert('✅ Beneficiary added successfully!');
        hideBeneficiaryForm();
        loadBeneficiaries();
    } catch (error) {
        console.error('Error creating beneficiary:', error);
        alert('❌ Error creating beneficiary: ' + error.message);
    }
}

// ============================================
// MEDIA GALLERY FUNCTIONS
// ============================================

// Load Media Galleries
async function loadMedia() {
    try {
        const response = await fetch(`${API_BASE}media_gallery?per_page=20`);
        const galleries = await response.json();
        displayMedia(galleries);
    } catch (error) {
        console.error('Error loading media galleries:', error);
        document.getElementById('media-list').innerHTML = '<div class="empty-state"><p>Error loading galleries</p></div>';
    }
}

// Display Media Galleries
function displayMedia(galleries) {
    const container = document.getElementById('media-list');
    
    if (!galleries || galleries.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>🖼️ No media galleries found. Create one!</p></div>';
        return;
    }

    container.innerHTML = galleries.map(item => {
        const title = item.title?.rendered || 'Untitled Gallery';
        const excerpt = item.content?.rendered ? item.content.rendered.replace(/<[^>]*>/g, '').substring(0, 100) : 'No description';
        const imageCount = item.media_gallery_images?.length || 0;
        const firstImage = item.media_gallery_images && item.media_gallery_images.length > 0 ? item.media_gallery_images[0] : '';
        
        return `
            <div class="item-card" onclick="showMediaDetail(${item.id})">
                <div class="item-header">
                    <h3 class="item-title">${title}</h3>
                </div>
                <div class="item-meta">
                    <span>📸 ${imageCount} images</span>
                </div>
                ${firstImage ? `<img src="${firstImage}" class="item-image" style="max-height: 200px;">` : '<div style="height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">📷 No images</div>'}
                <div class="item-excerpt">${excerpt}...</div>
                <div class="item-actions">
                    <button class="btn btn-primary" onclick="event.stopPropagation(); showMediaDetail(${item.id})">View Gallery</button>
                </div>
            </div>
        `;
    }).join('');
}

// Search Media
async function searchMedia() {
    const searchTerm = document.getElementById('media-search').value;
    if (!searchTerm) {
        loadMedia();
        return;
    }

    try {
        const response = await fetch(`${API_BASE}media_gallery?search=${encodeURIComponent(searchTerm)}`);
        const galleries = await response.json();
        displayMedia(galleries);
    } catch (error) {
        console.error('Error searching media:', error);
    }
}

// Show Media Detail
async function showMediaDetail(galleryId) {
    try {
        const response = await fetch(`${API_BASE}media_gallery/${galleryId}`);
        const item = await response.json();
        
        let imagesHtml = '';
        if (item.media_gallery_images && item.media_gallery_images.length > 0) {
            imagesHtml = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 20px;">';
            item.media_gallery_images.forEach(imageUrl => {
                imagesHtml += `<img src="${imageUrl}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">`;
            });
            imagesHtml += '</div>';
        }
        
        const content = `
            <h2>${item.title.rendered}</h2>
            <div class="item-meta">
                <span>📸 ${item.media_gallery_images?.length || 0} images</span>
            </div>
            <div>${item.content?.rendered || 'No description available'}</div>
            ${imagesHtml}
        `;
        
        document.getElementById('modal-body').innerHTML = content;
        document.getElementById('detail-modal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading media detail:', error);
    }
}

// Show Media Form
function showMediaForm() {
    document.getElementById('media-form').style.display = 'block';
}

// Hide Media Form
function hideMediaForm() {
    document.getElementById('media-form').style.display = 'none';
    document.getElementById('gallery-name').value = '';
    document.getElementById('gallery-description').value = '';
    document.getElementById('gallery-images').value = '';
    document.getElementById('gallery-images-preview').innerHTML = '';
}

// Handle Media Gallery Image Preview
document.getElementById('gallery-images')?.addEventListener('change', function(e) {
    const files = e.target.files;
    const preview = document.getElementById('gallery-images-preview');
    preview.innerHTML = '';
    
    if (files && files.length > 0) {
        preview.innerHTML = `<p>${files.length} image(s) selected</p>`;
        for (let i = 0; i < files.length && i < 5; i++) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.createElement('img');
                img.src = event.target.result;
                img.style.maxHeight = '80px';
                img.style.marginRight = '10px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(files[i]);
        }
    }
});

// Handle Media Submit
async function handleMediaSubmit(event) {
    event.preventDefault();

    const galleryName = document.getElementById('gallery-name').value;
    const galleryDescription = document.getElementById('gallery-description').value;
    const imageFiles = document.getElementById('gallery-images').files;

    if (!galleryName) {
        alert('Please enter a gallery name');
        return;
    }

    if (imageFiles.length === 0) {
        alert('Please select at least one image');
        return;
    }

    try {
        // Upload all images
        const imageArray = [];
        for (let i = 0; i < imageFiles.length; i++) {
            const formData = new FormData();
            formData.append('file', imageFiles[i]);

            const mediaResponse = await fetch(`${API_BASE}media`, {
                method: 'POST',
                body: formData
            });

            if (mediaResponse.ok) {
                const media = await mediaResponse.json();
                imageArray.push(media.source_url);
            }
        }

        // Create gallery post with uploaded images
        const galleryData = {
            title: galleryName,
            content: galleryDescription || 'Gallery',
            status: 'publish',
            meta: {
                media_gallery_images: imageArray
            }
        };

        const galleryResponse = await fetch(`${API_BASE}media_gallery`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(galleryData)
        });

        if (!galleryResponse.ok) {
            throw new Error('Failed to create gallery');
        }

        alert('✅ Gallery created successfully with ' + imageArray.length + ' images!');
        hideMediaForm();
        loadMedia();
    } catch (error) {
        console.error('Error creating gallery:', error);
        alert('❌ Error creating gallery: ' + error.message);
    }
}
