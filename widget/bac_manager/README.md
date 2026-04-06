# BAC Manager Widget

A simple WordPress BAC (Bids and Commissions) document management system for uploading and displaying PDF files.

## Features

### BAC Uploader
- Upload BAC documents with title and PDF file
- Simple and clean upload form
- AJAX-powered upload with validation
- Publish immediately

### BAC Viewer
- Display BAC documents in a list format
- Search functionality for BAC documents
- Download PDF files
- Edit and delete capabilities for administrators
- Responsive design

### BAC Manager
- Custom post type registration ('bac_item')
- Helper methods for retrieving BAC documents
- Recent BAC retrieval

## File Structure

```
bac_manager/
├── bac_uploader.php          # Uploader form and AJAX handlers
├── bac_viewer.php            # Viewer and display logic
├── bac_manager.php           # Main controller and initialization
├── bac_shortcodes.php        # Shortcode registration
├── init.php                  # Helper functions and admin menu
├── css/
│   ├── bac-upload-style.css  # Uploader styles
│   └── bac-viewer-style.css  # Viewer styles
├── js/
│   ├── bac-upload.js         # Uploader functionality
│   └── bac-viewer.js         # Viewer functionality
└── README.md
```

## Usage

### Initialize BAC Manager

Add this to your main plugin file:

```php
require_once plugin_dir_path(__FILE__) . 'widget/bac_manager/init.php';
```

### Display Upload Form

```php
display_bac_uploader();
```

### Display BAC List

```php
display_bac_viewer(array(
    'posts_per_page' => 10
));
```

## Shortcodes

### Upload Form
```
[bac_uploader]
```
*Note: Only visible to administrators*

### View List
```
[bac_viewer]
[bac_viewer posts_per_page="15"]
```

## Admin Menu

After including the BAC Manager:
- Go to **WordPress Admin** → **BAC Manager** menu
- Upload BAC documents
- View all BAC documents
- Edit or delete items

## Database

BAC items are stored as custom posts with post meta:
- `bac_pdf_id` - PDF attachment ID
- `bac_date` - Upload date

## Security

- AJAX requests use WordPress nonces
- User capability checks (admin only for editing/deleting)
- Sanitization of all user inputs
- XSS protection

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Responsive design for mobile browsers

## Dependencies

- WordPress 5.0+
- jQuery (included with WordPress)
- WordPress Media Library

## License

This widget is part of the Camaligan Customization plugin.
