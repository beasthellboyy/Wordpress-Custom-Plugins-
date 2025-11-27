# MasterStudy Custom Layout Plugin

This plugin allows you to override MasterStudy LMS templates with custom layouts and overrides only the course player file.

## File Structure

```
masterstudy-custom-layout/
├── masterstudy-custom-layout.php  # Main plugin file
├── assets/
│   ├── css/
│   │   └── custom-style.css       # Custom CSS styles
│   └── js/
│       └── custom-script.js       # Custom JavaScript
├── templates/
│   ├── course-player.php          # Main course player template
│   ├── course-player/
│   │   ├── header.php             # Course player header
│   │   ├── navigation.php         # Course player navigation
│   │   └── curriculum.php         # Course curriculum
│   └── components/
│       └── tabs.php               # Custom tabs component
└── README.md                      # This file
```

## How It Works

The plugin uses multiple methods to override MasterStudy LMS templates:

1. **WordPress Template System**: Hooks into `template_include` filter
2. **MasterStudy Filters**: Attempts to hook into various MasterStudy template filters
3. **STM_LMS_Templates**: If available, hooks into the STM template system

## Template Override Priority

Templates are loaded in this order:
1. Your custom templates in `templates/` folder
2. MasterStudy Pro templates
3. MasterStudy Free templates
4. Default WordPress templates

## Troubleshooting

### Debug Mode
Add `?debug_templates=1` to any course page URL (as admin) to see debug information.

### Common Issues

1. **Templates not loading**: 
   - Check file permissions
   - Ensure template files exist in correct locations
   - Verify MasterStudy LMS is active

2. **Styles not applying**:
   - Check if CSS file exists and is accessible
   - Verify the page detection logic in `masterstudy_custom_layout_enqueue_styles()`
   - Use browser dev tools to check if CSS is loaded

3. **JavaScript not working**:
   - Check browser console for errors
   - Ensure jQuery is loaded
   - Verify script file path

### Template File Requirements

Each template file should:
- Start with `<?php` tag
- Include security check: `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- Follow MasterStudy's template structure
- Use proper WordPress functions and hooks

## Customization

### Adding New Templates
1. Create template file in appropriate folder under `templates/`
2. Follow MasterStudy's naming conventions
3. Test thoroughly

### Modifying Styles
Edit `assets/css/custom-style.css` to customize appearance.

### Adding JavaScript
Edit `assets/js/custom-script.js` for custom functionality.

## Testing Steps

1. **First Test**: The plugin is currently set to use `course-player-simple.php` for testing
   - Visit a course lesson page
   - You should see a debug message confirming the override works
   
2. **Switch to Full Template**: Once simple template works, edit the plugin file:
   - Change `course-player-simple.php` back to `course-player.php`
   - Test the full course player functionality

3. **Disable Override**: If issues persist, set `MASTERSTUDY_CUSTOM_LAYOUT_ENABLED` to `false`

## Common Errors

### "Argument #1 ($course_id) must be of type int, null given"
This happens when the template loads without proper MasterStudy context:
- **Solution**: Use the simple template first to test override mechanism
- **Cause**: Template loading on wrong pages or missing required variables
- **Fix**: The updated template now has proper validation and fallbacks

## Support

If templates aren't overriding:
1. Check WordPress debug log
2. Verify MasterStudy LMS version compatibility  
3. Test with default WordPress theme
4. Disable other plugins to check for conflicts
5. Use the simple template first to isolate issues
