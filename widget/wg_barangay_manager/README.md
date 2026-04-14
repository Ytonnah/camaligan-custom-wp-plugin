# Barangay Profile Manager

## Features
- Admin dashboard for barangay profiles
- REST API CRUD operations
- Frontend shortcode display

## Fields
- Name of Barangay
- Barangay Profile
- Image of Barangay
- Origin of Name
- Demographic Profile

## Admin Menu
Go to `Barangay Manager` in WP Admin.

## Shortcodes
```
[barangay_profile posts_per_page="12"]
[barangay_viewer posts_per_page="12"]
[barangay_uploader] (admin only)
```

## REST API
```
GET    /wp-json/wp/v2/barangay-profiles
POST   /wp-json/wp/v2/barangay-profiles
GET    /wp-json/wp/v2/barangay-profiles/{id}
PUT    /wp-json/wp/v2/barangay-profiles/{id}
DELETE /wp-json/wp/v2/barangay-profiles/{id}
```

## Example Body
```json
{
  "name": "Barangay Poblacion",
  "barangay_profile": "Profile content here.",
  "image_id": 123,
  "origin_of_name": "Origin content here.",
  "demographic_profile": "Demographic content here.",
  "status": "publish"
}
```