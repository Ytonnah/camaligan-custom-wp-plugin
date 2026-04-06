# News Manager Widget

A comprehensive WordPress news management system with upload and viewer functionality.

## Features

### News Uploader
- Upload news items with title, content, and featured images
- Categorize news (General, Announcement, Event, Update, Alert)
- Set priority levels (Normal, High, Urgent)
- Mark as featured
- Publish immediately or save as draft
- Set custom publication dates
- AJAX-powered upload form

### News Viewer
- Display news items in a beautiful list format
- Search functionality for news items
- Filter by category
- Responsive design
- Priority and featured badges
- Edit and delete capabilities for administrators
- Pagination support

### News Manager
- Custom post type registration ('news_item')
- Taxonomy support for news types
- Helper methods for retrieving news
- Featured news retrieval
- Category-based filtering
- Priority-based filtering

## File Structure

```
wg_news_manager/
├── news_uploader.php       # Uploader form and AJAX handlers
├── news_viewer.php         # Viewer and display logic
├── news_manager.php        # Main controller and initialization
├── css/
│   ├── news-upload-style.css      # Uploader styles
│   ├── news-viewer-style.css      # Viewer styles
│   └── news-single-style.css      # Single item styles
├── js/
│   ├── news-upload.js      # Uploader functionality
│   └── news-viewer.js      # Viewer functionality
└── README.md
```

## Usage

### Initialize News Manager

```php
require_once 'news_manager.php';
$news_manager = News_Manager::get_instance();
```

### Display Upload Form

```php
$uploader = $news_manager->get_uploader();
$uploader->display_upload_form();
```

### Display News List

```php
$viewer = $news_manager->get_viewer();
$viewer->display_news_viewer(array(
    'posts_per_page' => 10
));
```

### Display Single News Item

```php
$viewer->display_single_news($post_id);
```

### Get News Programmatically

```php
// Get all news
$news = News_Manager::get_news(array('posts_per_page' => 20));

// Get featured news
$featured = News_Manager::get_featured_news(5);

// Get by category
$announcements = News_Manager::get_news_by_category('announcement', 10);

// Get by priority
$urgent = News_Manager::get_news_by_priority('urgent', 5);

// Get recent
$recent = News_Manager::get_recent_news(5);
```

## AJAX Endpoints

### Upload News
- **Action**: `upload_news`
- **Method**: POST
- **Parameters**:
  - `news_title` - News title (required)
  - `news_content` - News content (required)
  - `news_category` - Category
  - `news_priority` - Priority level
  - `news_image_id` - Featured image ID
  - `news_date` - Publication date
  - `news_featured` - Featured flag
  - `news_active` - Active/publish flag

### Update News
- **Action**: `update_news`
- **Method**: POST
- **Parameters**: Same as upload, plus `post_id`

### Delete News
- **Action**: `delete_news`
- **Method**: POST
- **Parameters**: `post_id`

### Search News
- **Action**: `search_news`
- **Method**: POST
- **Parameters**: `search_term`

### Filter News
- **Action**: `filter_news`
- **Method**: POST
- **Parameters**: `category`, `priority`

## Styling

The system includes comprehensive CSS for:
- Upload form styling
- News list display
- Single news page
- Responsive design for mobile, tablet, and desktop
- Priority and category badges
- Button styles and interactions

## Security

- AJAX requests use WordPress nonces
- User capability checks (admin only for editing/deleting)
- Sanitization of all user inputs
- XSS protection with `wp_kses_post()`
- CSRF protection with nonces

## Customization

You can customize categories and priorities by editing the select options in:
- `news_uploader.php` - Upload form
- `news_viewer.php` - Viewer filters

## Database

News items are stored as custom posts with post meta:
- `news_category` - Item category
- `news_priority` - Priority level
- `news_featured` - Featured status
- `news_image_id` - Featured image attachment ID
- `news_date` - Custom publication date

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
