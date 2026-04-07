# Tourism Manager Widget

A WordPress tourism management widget for uploading, displaying, and managing tourism destinations.

## Features

- **Upload Tourism Destinations** - Add tourism attractions with title, description, type, location, rating, and featured image
- **View Tourism Items** - Display tourism destinations in a responsive grid layout
- **Filter by Type** - Filter destinations by category (Beach, Mountain, Cultural, Historic, Natural, Adventure, Other)
- **Search Functionality** - Search tourism destinations by keyword
- **Featured Destinations** - Mark destinations as featured for prominence
- **Rating System** - Rate destinations from 1-5 stars
- **Responsive Design** - Mobile-friendly layout

## Installation

1. Extract the folder to `/wp-content/plugins/camaligan-customization/widget/`
2. Include the init.php file in your plugin:

```php
require_once __DIR__ . '/widget/wg_tourism_manager/init.php';
```

## Usage

### Using Shortcodes

#### Display Tourism Uploader
```
[tourism_uploader]
```

#### Display Tourism Viewer
```
[tourism_viewer]
[tourism_viewer posts_per_page="20"]
```

#### Display Featured Tourism Only
```
[featured_tourism]
[featured_tourism posts_per_page="6"]
```

### Using PHP Functions

```php
// Display uploader form
display_tourism_uploader();

// Display tourism viewer
display_tourism_viewer(array(
    'posts_per_page' => 12,
    'featured_only' => false
));

// Display single tourism item
display_single_tourism($post_id);
```

## Custom Post Type

- **Post Type Slug:** `tourism_item`
- **Supports:** Title, Editor, Thumbnail, Custom Fields
- **Archive:** Yes

## Custom Taxonomy

- **Taxonomy:** `tourism_type`
- **Slug:** `tourism-type`

## Custom Fields (Post Meta)

- `tourism_type` - Type of tourism destination
- `tourism_location` - Location address or coordinates
- `tourism_rating` - Rating from 1-5
- `tourism_featured` - Whether destination is featured (1 or 0)
- `tourism_image_id` - Featured image attachment ID

## Destination Types

- Beach
- Mountain
- Cultural
- Historic
- Natural
- Adventure
- Other

## AJAX Actions

- `upload_tourism` - Create new tourism destination
- `update_tourism` - Update existing tourism destination
- `delete_tourism` - Delete tourism destination
- `search_tourism` - Search destinations
- `filter_tourism` - Filter by type

## Files

- `tourism_manager.php` - Main controller
- `tourism_uploader.php` - Upload functionality
- `tourism_viewer.php` - Display functionality
- `tourism_shortcodes.php` - Shortcode registration
- `init.php` - Initialization and helper functions
- `css/tourism-upload-style.css` - Upload form styling
- `css/tourism-viewer-style.css` - Viewer styling
- `css/tourism-single-style.css` - Single item styling
- `js/tourism-upload.js` - Upload form functionality
- `js/tourism-viewer.js` - Viewer functionality

## Security

- All AJAX requests require nonce verification
- User capability check (only administrators can upload/edit/delete)
- Sanitization of all inputs
- Escaping of all outputs

## Credits

Created as part of the Camaligan Customization Plugin
