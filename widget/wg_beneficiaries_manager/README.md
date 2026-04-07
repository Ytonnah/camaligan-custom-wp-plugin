# Beneficiaries Manager

A comprehensive WordPress management system for tracking and displaying community beneficiaries with complete CRUD functionality.

## Features

### 📋 Add Beneficiary
- **Beneficiary Information**: Name, detailed description, barangay, contact number
- **Classification**: Type (Individual, Family, Organization, Community)
- **Program Tracking**: Program/assistance name, registration date, status (Active/Inactive/Pending)
- **Photo Upload**: Profile photo with media library integration
- **Real-time validation**: Required fields enforcement

### 👥 View Beneficiaries
- **Grid Display**: Responsive card layout with photos and status badges
- **Search**: Real-time search across beneficiary names
- **Filter**: Filter by beneficiary type and status
- **Detail View**: Modal popup with complete beneficiary information
- **CRUD Operations**: View, Edit, and Delete from the interface

### 🎯 Shortcodes

```php
// Display all beneficiaries
[beneficiaries_list]

// Display beneficiaries by type
[beneficiaries_by_type type="Individual"]
[beneficiaries_by_type type="Family"]
[beneficiaries_by_type type="Organization"]
[beneficiaries_by_type type="Community"]

// Display beneficiaries by barangay
[beneficiaries_by_barangay barangay="Poblacion"]
```

## Database Structure

- **Post Type**: `beneficiary_item` - Storage for beneficiary records
- **Post Meta Fields**:
  - `beneficiary_barangay` - Barangay name
  - `beneficiary_type` - Type (Individual/Family/Organization/Community)
  - `beneficiary_contact` - Contact number
  - `beneficiary_program` - Program/assistance name
  - `beneficiary_date` - Registration date
  - `beneficiary_status` - Status (Active/Inactive/Pending)

## File Structure

```
wg_beneficiaries_manager/
├── init.php                            # CPT registration & helper functions
├── beneficiaries_uploader.php          # CRUD upload handlers
├── beneficiaries_viewer.php            # Display & search
├── beneficiaries_shortcodes.php        # Shortcode definitions
├── mainmenu.php                        # Admin dashboard
├── README.md                           # This file
├── css/
│   ├── beneficiaries-upload-style.css  # Form styling
│   ├── beneficiaries-viewer-style.css  # List & modal styling
│   └── beneficiaries-shortcode-style.css
└── js/
    ├── beneficiaries-upload.js         # Form handling
    └── beneficiaries-viewer.js         # Viewer interactions
```

## CRUD Operations

### CREATE - Add New Beneficiary
1. Go to: **Camaligan's Custom Functions > Beneficiaries > Add Beneficiary**
2. Fill out form: Name, Description, Barangay, Type, Contact, Program, Date, Status
3. Upload optional profile photo
4. Click "Add Beneficiary"
5. Success message displays, form resets

**Form Fields Required**:
- Name
- Description
- Barangay
- Type (select)
- Program/Assistance
- Date Registered
- Status (select)

**Optional Fields**:
- Contact Number
- Profile Photo

### READ - View Beneficiaries

#### List View
- **Search**: Type beneficiary name in search box (real-time)
- **Type Filter**: Filter by Individual/Family/Organization/Community
- **Status Filter**: Filter by Active/Inactive/Pending
- **Detail View**: Click "View" button to see full details in modal

#### Detail Modal
Shows:
- Profile photo
- Name with type and status badges
- Barangay location
- Type and status
- Program/assistance
- Contact number
- Registration date
- Full description

**Actions from Modal**:
- Edit button → Auto-populate form and scroll to top
- Delete button → Confirm deletion
- Close button → Close modal

### UPDATE - Edit Beneficiary
1. Click "Edit" button on beneficiary card or from detail modal
2. Form pre-fills with current data
3. Button text changes to "Update Beneficiary"
4. Modify fields as needed
5. Click "Update Beneficiary"
6. Page reloads with updated information

### DELETE - Remove Beneficiary
1. Click "Delete" button on card or from modal
2. Confirmation dialog appears
3. If confirmed, beneficiary is permanently deleted
4. List refreshes automatically

## AJAX Actions

All operations use `beneficiaries_nonce` for security:

| Action | Purpose | Handler |
|--------|---------|---------|
| `upload_beneficiary` | Add new beneficiary | `handle_beneficiary_upload()` |
| `update_beneficiary` | Update existing record | `handle_beneficiary_update()` |
| `delete_beneficiary` | Delete beneficiary | `handle_beneficiary_delete()` |
| `get_beneficiary_detail` | Fetch full details | `handle_get_beneficiary_detail()` |
| `search_beneficiaries` | Search by name | `handle_search_beneficiaries()` |
| `filter_beneficiaries` | Filter by type/status | `handle_filter_beneficiaries()` |

## Admin Dashboard

Located at: **Camaligan's Custom Functions > Beneficiaries**

### Tabs
1. **Add Beneficiary** - Form to add/edit beneficiaries
2. **View Beneficiaries** - List with search and filters

### Features
- Tabbed navigation interface
- Real-time search
- Dual filtering (type + status)
- Inline action buttons
- Modal detail view
- Direct edit from list

## Helper Functions

### Query Functions
```php
get_all_beneficiaries($per_page = -1);               // Get all beneficiaries
get_beneficiary($id);                                 // Get single beneficiary
get_beneficiaries_by_type($type);                     // Filter by type
get_beneficiaries_by_barangay($barangay);            // Filter by barangay
get_beneficiaries_by_status($status);                 // Filter by status
```

### Utility Functions
```php
get_all_barangays();                                  // Get unique barangays
get_beneficiary_count($status = '');                 // Count beneficiaries
```

## Security

- **Nonce Verification**: All AJAX calls use `beneficiaries_nonce`
- **Capability Check**: Only admin users (`manage_options`) can manage beneficiaries
- **Input Sanitization**: 
  - Text fields: `sanitize_text_field()`
  - Rich content: `wp_kses_post()`
- **Output Escaping**:
  - HTML: `esc_html()`
  - Attributes: `esc_attr()`
  - URLs: `esc_url()`

## Frontend Display

### Display All Beneficiaries
```php
[beneficiaries_list]
```
Shows all active beneficiaries in a responsive grid layout.

### Display by Type
```php
[beneficiaries_by_type type="Individual"]
```
Shows only beneficiaries matching the specified type.

**Valid Types**: Individual, Family, Organization, Community

### Display by Barangay
```php
[beneficiaries_by_barangay barangay="Poblacion"]
```
Shows only beneficiaries from the specified barangay.

## Responsive Design

- **Desktop**: Full-width grid with 2-4 columns
- **Tablet**: 2-column layout
- **Mobile**: Single column with optimized touch controls

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Data Structure Example

```php
[
    'ID' => 1,
    'name' => 'John Doe',
    'description' => 'Single parent needing livelihood assistance...',
    'barangay' => 'Poblacion',
    'type' => 'Individual',
    'contact' => '09123456789',
    'program' => 'Emergency Food Assistance',
    'date' => '2026-04-07',
    'status' => 'Active',
    'image_id' => 123
]
```

## Workflow Example

1. **Create**: Admin adds beneficiary record with all details
2. **Assign**: Link beneficiary to specific program/assistance
3. **Display**: Show beneficiary on public pages via shortcodes
4. **Update**: Modify beneficiary status or program as needed
5. **Archive**: Mark as Inactive when assistance completes
6. **Report**: Query all beneficiaries by type or barangay

## Tips & Best Practices

- Use consistent barangay names for better filtering
- Set status to "Pending" for verification period
- Include detailed descriptions for context
- Update contact information regularly
- Use Type field for proper categorization
- Mark as Inactive when no longer active recipient

## Performance Notes

- All searches use WordPress `get_posts()` with optimized queries
- Meta queries indexed for filter performance
- AJAX handlers use proper nonce verification
- Images lazy-loaded in frontend display

## Future Enhancements

- [ ] Bulk import from CSV
- [ ] Export beneficiary list
- [ ] Program/assistance categorization
- [ ] Beneficiary statistics dashboard
- [ ] Approval workflow
- [ ] Beneficiary history tracking
- [ ] Photo gallery per beneficiary
- [ ] Geographic mapping

---

**Version**: 1.0.0  
**Last Updated**: April 7, 2026  
**Author**: Camaligan Plugin Suite
