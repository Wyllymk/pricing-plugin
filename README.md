.# Nuvex Capital Challenge Card

A fully admin-configurable WordPress plugin for displaying trading challenge cards. Perfect for prop trading firms and forex brokers to showcase their evaluation programs.

## Features

### 🎯 **Challenge Types**

- **1-Step Challenge**: Single phase evaluation with Phase 1 + Funded account
- **2-Step Challenge**: Two-phase evaluation with Phase 1 + Phase 2 + Funded account
- **Flexible Configuration**: Enable/disable challenge types independently

### 💰 **Account Sizes & Pricing**

- Unlimited account sizes per challenge type
- Dynamic pricing with discount support
- Currency support (€ or $)
- Featured account highlighting with badges
- Direct checkout links (full URLs or placeholders)

### 📊 **Challenge Metrics**

- Customizable metrics table with icons
- Phase-specific values (Phase 1, Phase 2, Funded)
- Visual column differentiation with background colors
- Support for emoji, image URLs, or uploaded icons

### 💳 **Payment Methods**

- Multiple payment options display
- Flexible display modes: Icon + Text, Icon Only, or Text Only
- Icon support (emoji, URLs, uploads) with responsive sizing

### 🎨 **Customization**

- Accent color picker for branding
- Custom button text
- Responsive design with modern UI
- Mobile-friendly interface

### 🔧 **Admin Features**

- Intuitive tabbed settings interface
- Real-time preview of icons and changes
- Per-tab saving for efficient configuration
- WordPress media library integration for icon uploads

## Installation

1. Download the plugin files
2. Upload `nuvex-plugin.php` to your `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin dashboard
4. Navigate to **Nuvex Capital** in the admin menu to configure

## Usage

### Shortcode

Add the challenge card to any page or post using the shortcode:

```
[challenge_card]
```

### Configuration

#### General Settings

- **Accent Color**: Choose your brand color (applied to buttons and highlights)
- **Buy Button Text**: Customize the call-to-action button text
- **Currency Symbol**: Select € (Euro) or $ (Dollar)
- **Payment Methods**: Add payment options with icons and labels

#### Challenge Configuration (1-Step & 2-Step)

For each challenge type, configure:

- **Step Label**: Display name for the challenge type
- **Visibility**: Enable/disable the challenge type
- **Column Headers**: Customize Phase 1, Phase 2 (if applicable), and Funded column names

##### Account Sizes

- **Size Label**: Account size (e.g., "$10k", "50K")
- **Price**: Regular price
- **Discount Price**: Sale price (0 = no discount shown)
- **Checkout Link**: Full URL or "#" placeholder
- **Featured**: Highlight this size with special styling
- **Badge Text**: Custom badge for featured accounts

##### Challenge Metrics

- **Icon**: Emoji, image URL, or uploaded image (24×24px display)
- **Metric Label**: Description of the metric
- **Phase Values**: Specific values for each phase and funded account

## Default Configuration

### 1-Step Challenge

- **Account Sizes**: $7.5k, $10k, $25k, $50k (featured), $100k, $200k
- **Metrics**: Target (10%), Daily Drawdown (5%), Maximum Drawdown (10%), Trading Period (No limit), Minimum Trading Days (4), Refundable (Yes)

### 2-Step Challenge

- **Account Sizes**: 5K, 10K, 50K, 100K (featured), 150K, 200K
- **Metrics**: Target (8%/5%), Daily Drawdown (5%), Maximum Drawdown (10%), Trading Period (No time limit), Minimum Trading Days (3), Refundable Fee (Yes/-)

## Technical Details

### Requirements

- WordPress 5.0+
- PHP 7.4+
- Admin access for configuration

### Security

- Nonce verification for all admin actions
- Input sanitization and validation
- Escaped output to prevent XSS

### Performance

- Lightweight JavaScript for interactive features
- CSS custom properties for dynamic theming
- Efficient data structure with minimal database queries

### Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile devices
- Graceful degradation for older browsers

## Customization

### Styling

The plugin uses CSS custom properties for easy theming:

```css
:root {
  --ncc: #7c3aed; /* Accent color */
  --ncc-rgb: 124, 58, 237; /* RGB values for transparency */
}
```

### Hooks & Filters

The plugin provides several WordPress hooks for advanced customization:

- `nuvex_challenge_settings` - Filter default settings
- `nuvex_shortcode_output` - Modify shortcode HTML output
- `nuvex_admin_scripts` - Add custom admin scripts

## Support

For support, feature requests, or bug reports:

- Check the WordPress admin for error messages
- Verify plugin settings are saved correctly
- Ensure shortcode is properly placed in content

## Changelog

### Version 1.0.0

- Initial release with full challenge card functionality
- Admin configuration interface
- Responsive frontend design
- Currency and payment method support

## License

This plugin is provided as-is. Please ensure compliance with your local laws and regulations regarding financial services advertising.

## Author

**Trading Tech**

- Plugin development and maintenance

---

_Display professional trading challenges with style and ease._
