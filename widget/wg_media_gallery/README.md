# Media Gallery Manager

A comprehensive WordPress media management system for uploading, organizing, and displaying image galleries with full CRUD functionality.

## Features

### 📤 Upload Media
- **Multiple file uploads** - Upload several images at once to galleries
- **Gallery organization** - Create and manage multiple galleries
- **Image captions** - Add alt text and captions to images
- **Progress tracking** - Real-time upload progress indicator

### 👁️ View Galleries
- **Gallery list view** - Browse all galleries in a responsive grid
- **Search functionality** - Real-time search across galleries
- **Image details** - View full-size images with captions
- **Gallery management** - Delete galleries or individual images

### 🎯 Shortcodes

```php
// Display specific gallery by ID
[media_gallery id="123"]

// Display all galleries as preview cards
[gallery_list]

// Display gallery as slider/carousel
[gallery_slider id="123"]
```

### 💾 Database Structure

- **Post Type**: `media_gallery` - Storage for gallery metadata
- **Post Meta Fields**:
  - `gallery_images` - Array of image attachment IDs
  - `_wp_attachment_image_alt` - Image captions/alt text

## File Structure

```
wg_media_gallery/
├── init.php                            # Helper functions & CPT registration
├── media_gallery_uploader.php          # Image upload & gallery CRUD
├── media_gallery_viewer.php            # Gallery display & search
├── media_gallery_shortcodes.php        # Shortcode definitions
├── mainmenu.php                        # Admin dashboard tabbed interface
├── README.md                           # This file
├── css/
│   ├── media-gallery-common.css        # Common button/form styles
│   ├── media-gallery-style.css         # Uploader interface styles
│   ├── media-gallery-viewer-style.css  # Viewer & modal styles
│   ├── media-gallery-shortcode-style.css
│   ├── media-gallery-list-style.css
│   └── media-gallery-slider-style.css
└── js/
    ├── media-gallery.js                # Uploader interactions
    ├── media-gallery-viewer.js         # Viewer interactions
    └── media-gallery-slider.js         # Slider functionality
```

## AJAX Actions

All operations use `media_gallery_nonce` for security:

| Action | Purpose | Handler |
|--------|---------|---------|
| `upload_gallery_image` | Upload images to gallery | `handle_image_upload()` |
| `delete_gallery_image` | Remove image from gallery | `handle_image_delete()` |
| `create_gallery` | Create new gallery | `handle_create_gallery()` |
| `get_gallery_images` | Fetch gallery images | `handle_get_gallery_images()` |
| `update_image_caption` | Update image alt text | `handle_update_caption()` |
| `search_galleries` | Search galleries | `handle_search_galleries()` |
| `delete_gallery` | Delete entire gallery | `handle_delete_gallery()` |

## Admin Dashboard

Located at: **Camaligan's Custom Functions > Media Gallery**

### Tabs
1. **Upload Media** - Create galleries and upload images
2. **View Galleries** - Browse, edit captions, and manage galleries

### Features
- Gallery selector dropdown
- Quick "New Gallery" button
- Bulk image upload with progress bar
- Gallery images grid with edit/delete buttons
- Modal image viewer with caption editing

## Usage

### Creating a Gallery
1. Go to Admin Dashboard > Media Gallery
2. Click "New Gallery"
3. Enter gallery name and optional description
4. Click "Create Gallery"

### Uploading Images
1. Select or create a gallery
2. Click "Select Images"
3. Choose multiple images
4. Click "Upload Images"
5. Track progress in real-time

### Managing Galleries
1. Go to "View Galleries" tab
2. Use search to find galleries
3. Click "View" to see images
4. Click image to view full size
5. Edit captions in modal
6. Click "Delete" to remove gallery

### Frontend Display

#### Gallery Grid
```php
[media_gallery id="123"]
```
Displays gallery images in a clickable grid. Click images to view full size.

#### Gallery List
```php
[gallery_list]
```
Shows all galleries as preview cards with overlays displaying gallery info.

#### Gallery Slider
```php
[gallery_slider id="123"]
```
Displays gallery as a carousel with navigation arrows and dot indicators.

## Security

- **Nonce Verification**: All AJAX calls use `media_gallery_nonce`
- **Capability Check**: Only admin users (`manage_options`) can manage galleries
- **Input Sanitization**: All inputs sanitized with `sanitize_text_field()` or `wp_kses_post()`
- **Output Escaping**: All outputs escaped with `esc_html()`, `esc_url()`, or `esc_attr()`

## Helper Functions

### Get Gallery Data
```php
get_media_gallery($gallery_id);
// Returns: array with id, title, description, images, image_count
```

### Get All Galleries
```php
get_all_media_galleries($per_page = -1);
// Returns: array of all galleries with data
```

### Display Images
```php
display_gallery_images($gallery_id, $class = '');
// Returns: HTML string with gallery images
```

## Responsive Design

- **Desktop**: Grid layouts optimized for full-size viewing
- **Tablet**: Adjusted grid columns for medium screens
- **Mobile**: Single column layout with touch-friendly controls

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Working mobile browsers

## Notes

- Images are stored as WordPress attachments
- Galleries are stored as `media_gallery` custom post type
- Image associations stored in post meta
- Deleting a gallery removes associations but images remain in media library
- Supports all common image formats (JPEG, PNG, GIF, WebP)

## Future Enhancements

- [ ] Drag-to-reorder gallery images
- [ ] Bulk image editing (crop, filter)
- [ ] Gallery categories/taxonomy
- [ ] Image permissions/privacy levels
- [ ] Gallery templates/themes
- [ ] Social sharing integration
- [ ] Image lazy loading

---

**Version**: 1.0.0  
**Last Updated**: April 7, 2026  
**Author**: Camaligan Plugin Suite
