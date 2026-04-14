# WG Barangay Manager

## Features
- Admin dashboard for uploading barangay profiles (demographics, patron saint, topography, images)
- Frontend shortcodes for displaying profiles
- AJAX CRUD operations
- Search/filter featured

## Admin Menu
Go to `Barangay Manager` in WP Admin.

## Shortcodes
```
[barangay_viewer posts_per_page="12"]
[featured_barangay posts_per_page="4"]
[barangay_uploader] (admin only)
```

## Fields
- Barangay Name
- Description
- Demographics
- Patron Saint
- Topography
- Location
- Population
- Featured Image
- Featured toggle

## CPT
`barangay_profile` - auto registered.
