# News Manager Widget - Implementation Guide

## Quick Start

### 1. Include the News Manager in Your Plugin

Add this line to your main plugin file (`camaligan-customization.php`):

```php
// Include News Manager
require_once plugin_dir_path(__FILE__) . 'widget/wg_news_manager/init.php';
```

### 2. Use Shortcodes on Pages/Posts

#### Display News Upload Form
```
[news_uploader]
```
*Note: Only visible to administrators*

#### Display News List with Filters
```
[news_viewer]
[news_viewer posts_per_page="15"]
```

#### Display Featured News Only
```
[featured_news]
[featured_news limit="3"]
```

## Template Usage

### Display in Custom Templates

```php
<?php
// Display news uploader form
display_news_uploader();

// Display news viewer
display_news_viewer(array(
    'posts_per_page' => 10,
    'featured_only' => false
));

// Display single news item
display_single_news($post_id);
?>
```

## Programmatic Usage Examples

### Get Recent News
```php
$query = News_Manager::get_recent_news(5);
while ($query->have_posts()) {
    $query->the_post();
    echo get_the_title();
}
```

### Get Featured News
```php
$featured = News_Manager::get_featured_news(3);
while ($featured->have_posts()) {
    $featured->the_post();
    echo get_the_title();
}
```

### Get News by Category
```php
$announcements = News_Manager::get_news_by_category('announcement', 10);
```

### Get News by Priority
```php
$urgent = News_Manager::get_news_by_priority('urgent');
```

### Get All News with Custom Query
```php
$args = array(
    'posts_per_page' => 20,
    'orderby' => 'date',
    'order' => 'DESC'
);
$news = News_Manager::get_news($args);
```

## Admin Menu

After including the News Manager, three admin menu items are automatically added:

1. **News Manager** - Dashboard with recent news summary
2. **Upload News** - Form to add new news items
3. **View News** - List of all news items with edit/delete options

## Available Categories

- General
- Announcement
- Event
- Update
- Alert

## Available Priority Levels

- Normal
- High
- Urgent

## Customizing Categories and Priorities

Edit the select options in `news_uploader.php`:

```php
<select id="news_category" name="news_category">
    <option value="">Select Category</option>
    <option value="general">General</option>
    <option value="announcement">Announcement</option>
    <!-- Add more categories here -->
</select>
```

## Database Structure

News items are stored as custom posts:
- **Post Type**: `news_item`
- **Post Meta**:
  - `news_category` - Category slug
  - `news_priority` - Priority level
  - `news_featured` - Featured status (0 or 1)
  - `news_image_id` - Featured image attachment ID
  - `news_date` - Custom publication date

## AJAX Endpoints

All AJAX endpoints are prefixed with `wp_ajax_`:

- `upload_news` - Create new news item
- `update_news` - Update existing news item
- `delete_news` - Delete news item
- `search_news` - Search news items
- `filter_news` - Filter news items

## Security

- All AJAX requests are protected with WordPress nonces
- User capabilities are checked (admin-only for editing/deleting)
- All user input is sanitized and escaped
- Uses WordPress security best practices

## Styling

The widget includes CSS files for:
- Upload form
- News list view
- Single news item view
- Responsive design

You can override styles in your theme's `style.css` by using more specific selectors.

### CSS Classes

**Container Classes:**
- `.news-uploader-container`
- `.news-viewer-container`
- `.news-single`

**News Item Classes:**
- `.news-item`
- `.priority-urgent`, `.priority-high`, `.priority-normal`
- `.news-category`, `.category-general`, `.category-announcement`, etc.
- `.featured-badge`

**Form Classes:**
- `.news-form`
- `.form-group`
- `.form-actions`

## Troubleshooting

### Upload Form Not Showing
- Verify you're logged in as an admin
- Check that the init.php file is included

### Media Upload Not Working
- Ensure `wp_enqueue_media()` is being called
- Check browser console for JavaScript errors

### News Not Displaying
- Verify news items have `post_status` set to 'publish'
- Check that CSS files are loading properly
- Ensure no JavaScript conflicts with jQuery

### AJAX Requests Failing
- Verify nonces are being generated correctly
- Check that admin-ajax.php is accessible
- Look at browser Network tab to see AJAX responses

## Performance Tips

1. **Limit posts per page** - Use `posts_per_page` parameter to avoid loading too many items
2. **Use featured news** - Display featured items instead of all news
3. **Cache results** - Consider using WordPress transients for frequently accessed queries
4. **Optimize images** - Use WordPress image optimization plugins
5. **Lazy loading** - Consider lazy loading for image-heavy pages

## Future Enhancements

Potential features for expansion:
- RSS feed generation
- Email notifications
- Social media sharing
- Comments/discussion
- Related news items
- View statistics/tracking
- Export news to PDF
- Multiple languages support
- Advanced scheduling
- Bulk actions

## Support

For issues or questions about the News Manager Widget, review the code documentation in each PHP file or check the generated README.md.
