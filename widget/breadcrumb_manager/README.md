# Breadcrumb Manager Widget

## Features
- Customizable breadcrumb navigation
- Widget and shortcode support
- Options: separator, home link, current page, labels
- Responsive design
- ARIA labels for accessibility

## Usage

### Widget
1. Go to Appearance > Widgets
2. Add "Breadcrumb Navigator" to sidebar
3. Configure options

### Shortcode
```
[breadcrumb]
[breadcrumb separator=" > " show_home="true"]
[breadcrumb home_label="Home" separator=" | "]
```

## Options
- `separator`: Text between crumbs (default: / )
- `show_home`: Show home link (true/false)
- `show_current`: Show current page title (true/false)
- `home_label`: Home link text

## CSS Classes
- `.breadcrumb-nav`
- `.breadcrumb-list`
- `.breadcrumb-list a`

