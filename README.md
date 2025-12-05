# Customizer Backup & Reset

A lightweight WordPress plugin that adds reset, backup, and import/export functionality to the WordPress Customizer.

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/customizer-reset-by-wpzoom)](https://wordpress.org/plugins/customizer-reset-by-wpzoom/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/customizer-reset-by-wpzoom)](https://wordpress.org/plugins/customizer-reset-by-wpzoom/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/customizer-reset-by-wpzoom)](https://wordpress.org/plugins/customizer-reset-by-wpzoom/)
[![License](https://img.shields.io/badge/license-GPL--3.0%2B-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.html)

## The Problem

WordPress Customizer is missing some basic features:

- **No reset button** - Can't easily start fresh with theme defaults
- **No backup** - Make a change you regret? Too bad
- **No export/import** - Want to move settings between sites? Good luck

## The Solution

This plugin adds all of that in a clean, lightweight package (~40 KB).

## Features

### Reset Customizer
- One-click reset to theme defaults
- Optional: Also reset Additional CSS
- Works with popular themes (Divi, Astra, GeneratePress, and more)

### Backup System
- Automatic backup before reset
- Manual backup creation
- 5 rotating backups stored for 30 days
- One-click restore from any backup

### Import & Export
- Export settings to JSON or DAT format
- Drag & drop file import
- Compatible with "Customizer Export/Import" plugin files
- Option to download and import images from remote URLs

### Additional CSS Support
- Included in exports and backups
- Optional reset checkbox
- Preserved during restore

## Installation

### From WordPress.org

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Customizer Backup & Reset"
3. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin from [WordPress.org](https://wordpress.org/plugins/customizer-reset-by-wpzoom/)
2. Upload to `/wp-content/plugins/customizer-reset-by-wpzoom/`
3. Activate through the **Plugins** menu

### From GitHub

```bash
cd wp-content/plugins
git clone https://github.com/wpzoom/customizer-reset-by-wpzoom.git
```

## Usage

1. Go to **Appearance → Customize**
2. Look for the **"Customizer Backup & Reset"** section at the bottom
3. Or click the **"Reset Tools"** button in the customizer header

### Reset Options

- **Backup & Reset Customizer** - Creates a backup, then resets (recommended)
- **Reset Customizer (No Backup)** - Resets immediately without backup
- **Also remove Additional CSS** - Check this to also clear Additional CSS

### Export/Import

- **Export** - Downloads your settings as JSON or DAT file
- **Import** - Click the button or drag & drop a file
- **Download and import image files** - Check this to also import images

### Backup History

- View all your backups with timestamps
- **Restore** any backup with one click
- **Create Backup** without resetting
- **Delete** individual backups or all at once

## Theme Compatibility

Works with 99% of themes. Special handling for:

- **Divi** - Preserves Theme Options while resetting Customizer settings
- **Astra** - Preserves theme version settings
- **GeneratePress** - Clears dynamic CSS cache for immediate frontend updates

> Using a theme that doesn't work? [Let us know](https://github.com/wpzoom/customizer-reset-by-wpzoom/issues)!

## Developer Hooks

### Filter: `customizer_reset_settings`

Modify which settings get reset:

```php
add_filter( 'customizer_reset_settings', function( $settings ) {
    // Remove specific settings from reset
    unset( $settings['my_setting_id'] );
    return $settings;
});
```

### Filter: `customizer_reset_export_option_keys`

Add custom options to export data:

```php
add_filter( 'customizer_reset_export_option_keys', function( $option_keys ) {
    $option_keys[] = 'my_custom_option';
    return $option_keys;
});
```

### WordPress Hooks

During import, standard WordPress hooks are triggered:

- `customize_save` - Before all settings are saved
- `customize_save_{$setting_id}` - For each individual setting
- `customize_save_after` - After all settings are saved

## Requirements

- WordPress 6.4 or higher
- PHP 7.4 or higher

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development

```bash
# Install dependencies
composer install

# Check coding standards
composer run phpcs

# Auto-fix coding standards
composer run phpcbf

# Run static analysis
composer run phpstan
```

## Changelog

### 2.0.3
- NEW: Added "Create Backup" button to save backups without resetting
- Fixed multisite compatibility

### 2.0.2
- Minor bug fix in WP 6.9

### 2.0.1
- Minor bug fix

### 2.0.0
- NEW: Redesigned UI with new customizer section panel
- NEW: Import/Export with dual format support (JSON and DAT)
- NEW: Additional CSS reset option
- NEW: Backup system with 5 rotating backups (30 days)
- Added GeneratePress theme compatibility
- Added translation support

[View full changelog](https://wordpress.org/plugins/customizer-reset-by-wpzoom/#developers)

## Credits

Developed by [WPZOOM](https://www.wpzoom.com/)

## License

This plugin is licensed under the [GPL v3 or later](https://www.gnu.org/licenses/gpl-3.0.html).

---

**Links:** [WordPress.org](https://wordpress.org/plugins/customizer-reset-by-wpzoom/) · [WPZOOM](https://www.wpzoom.com/) · [Support](https://wordpress.org/support/plugin/customizer-reset-by-wpzoom/)
