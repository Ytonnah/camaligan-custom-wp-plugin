// Budget Overview CRUD Functions
// Endpoint: /wp/v2/budget_overview

// Load Budget Overview
async function loadBudget() {
    try {
        const response = await fetch(`${API_BASE}budget_overview?per_page=20`);
        const budgets = await response.json();
        if (budgets.items) budgets = budgets.items;
        displayBudget(budgets);
    } catch (error) {
        console.error('Error loading budget:', error);
        document.getElementById('budget-list').innerHTML = '<div class="empty-state"><p>Error loading budget overview</p></div>';
    }
}

// Display Budget Overview
function displayBudget(budgets) {
    const container = document.getElementById('budget-list');
    
    if (!budgets || budgets.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>💰 No budget overview items found</p></div>';
        return;
    }

    container.innerHTML = budgets.map(item => {
        const pdfUrl = item.pdf_url || '#';
        return `
            <div class="item-card" onclick="showBudgetDetail(${item.id})">
                <div class="item-header">
                    <h3 class="item-title">${item.title}</h3>
                </div>
                <div class="item-meta">
                    <span>📅 ${item.year}</span>
                    <span class="item-badge">${item.status || 'Published'}</span>
                </div>
                <div class="item-excerpt">Ordinance: ${item.ordinance_no} | Budget: ₱${item.total_budget?.toLocaleString()}</div>
                <div class="item-actions">
                    <a href="${pdfUrl}" class="btn btn-primary" target="_blank">View PDF</a>
                    <button class="btn btn-danger" onclick="event.stopPropagation(); deleteBudget(${item.id})">Delete</button>
                </div>
            </div>
        `;
    }).join('');
}

// Search Budget
async function searchBudget() {
    const searchTerm = document.getElementById('budget-search').value;
    if (!searchTerm) {
        loadBudget();
        return;
    }

    try {
        const response = await fetch(`${API_BASE}budget_overview?search=${encodeURIComponent(searchTerm)}`);
        const budgets = await response.json();
        if (budgets.items) budgets = budgets.items;
        displayBudget(budgets);
    } catch (error) {
        console.error('Error searching budget:', error);
    }
}

// Show Budget Detail
async function showBudgetDetail(id) {
    try {
        const response = await fetch(`${API_BASE}budget_overview/${id}`);
        const item = await response.json();
        const content = `
            <h2>${item.title}</h2>
            <div class="item-meta">
                <span>📅 Year: ${item.year}</span>
                <span>📄 Ordinance: ${item.ordinance_no}</span>
                <span>💰 Total: ₱${item.total_budget?.toLocaleString()}</span>
            </div>
            ${item.pdf_url ? `<iframe src="${item.pdf_url}" style="width:100%; height:500px;" frameborder="0"></iframe>` : '<p>No PDF available</p>'}
        `;
        showModal(content);
    } catch (error) {
        console.error('Error loading budget detail:', error);
    }
}

// Delete Budget
async function deleteBudget(id) {
    if (!confirm('Are you sure you want to delete this budget overview?')) return;
    try {
        await fetch(`${API_BASE}budget_overview/${id}`, { method: 'DELETE' });
        loadBudget();
    } catch (error) {
        alert('Error deleting budget item');
    }
}

// Show/Hide Budget Form
function showBudgetForm() {
    document.getElementById('budget-form').style.display = 'block';
}

function hideBudgetForm() {
    document.getElementById('budget-form').style.display = 'none';
    document.getElementById('budget-title').value = '';
    document.getElementById('budget-year').value = '';
    document.getElementById('budget-ordinance-no').value = '';
    document.getElementById('budget-total').value = '';
    document.getElementById('budget-pdf').value = '';
    document.getElementById('budget-status').value = 'publish';
}

// Handle Budget PDF Preview
document.getElementById('budget-pdf')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || '';
    if (fileName) {
        const preview = document.createElement('p');
        preview.textContent = `Selected: ${fileName}`;
        preview.style.color = '#28a745';
        e.target.parentNode.appendChild(preview);
    }
});

// Handle Budget Submit
async function handleBudgetSubmit(event) {
    event.preventDefault();
    const title = document.getElementById('budget-title').value;
    const year = document.getElementById('budget-year').value;
    const ordinanceNo = document.getElementById('budget-ordinance-no').value;
    const total = document.getElementById('budget-total').value;
    const status = document.getElementById('budget-status').value;
    const pdfFile = document.getElementById('budget-pdf').files[0];

    if (!title || !year || !ordinanceNo || !total || !pdfFile) {
        alert('All fields required');
        return;
    }

    try {
        // Upload PDF first
        const formData = new FormData();
        formData.append('file', pdfFile);
        const mediaResponse = await fetch(`${API_BASE}media`, { method: 'POST', body: formData });
        const media = await mediaResponse.json();
        const pdfId = media.id;

        // Create budget overview
        const budgetData = {
            title,
            year: parseInt(year),
            ordinance_no: ordinanceNo,
            total_budget: total,
            pdf_id: pdfId,
            status
        };

        const response = await fetch(`${API_BASE}budget_overview`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(budgetData)
        });

        if (!response.ok) throw new Error('Failed to create budget overview');
        alert('✅ Budget overview added successfully!');
        hideBudgetForm();
        loadBudget();
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
    }
}

