# Taro Ad Fields

Contributors: tarosky, Takahashi_Fumiki, yocchi161  
Tags: advertisement  
Requires at least: 4.7.0  
Tested up to: 4.8.2  
Stable tag: 1.1.0  
Requires PHP: 5.4.0  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Advertise block manager for WordPress.

## Description

You can create ad slot for you WordPress theme.
In each position, the latest ad field will be displayed.
You can manage your advertisement's schedule & display!

### From Theme

Call action hook in your theme:

<pre>
&lt;?php do_action( 'taro_ad_field', 'after-header', '&lt;div class="after-header"&gt;', '&lt;/div&gt;' ); ?&gt;
</pre>

In this place, the latest ad will be displayed.
With [Taro Clockwork Post](https://wordpress.org/plugins/taro-clockwork-post/) plugin, you can let your ad be automatically expired.

#### Hook Arguments

`do_action( $hook_name, $slug, $before, $after );`

1. **$hook_name**: The action hook name. Always should be `taro_ad_field`.
2. **$slug**: Slug of position.
3. **$before**: String to be output just before ad block. If no ad exists, this won't be displayed.
4. **$after**: String to be output just after ad block.

### From Widget

We also have widget for ad field. The latest ad of specified position will be displayed in the widget.

### Set Default Positions

In your theme, add filter hook for `taf_default_positions`.
These terms will be created automatically.

<pre>
add_filter( 'taf_default_positions', function() {
	return [
		'after-header' => [
			'name' => 'After Header',
			'description' => 'Displayed just after header.',
		],
		'after-content' => [
			'name' => 'After Content',
			'description' => 'Displayed just after content.',
			'mode' => 'iframe',
		],
	];
} );
</pre>

Defaults are empty array, so no default position.
You can add new position to them with filter hook like above.
The structure of array will be like below:

<pre>
[
  'slug_name' => [
    'name'        => 'Verbose Postion Name',
    'description' => 'About where will be displayed(up to you)',
    'mode'        => 'iframe',
  ],
]
</pre>

If you set mode as 'iframe', this position will have URL and display ad in very simple HTML pages.
You can get URL from view link in position list of admin screen.

This feature is useful to deliver ad in external platform like [Facebook Instant Article](https://instantarticles.fb.com).

## Installation

1. Upload the plugin files to the `/wp-content/plugins/taro-ad-fields` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to `Ad Field > Position` and create positons. If you set default positions with filters, they will be automatically generated.
4. Register ad posions. The published fields will be displayed.

## Frequently Asked Questions

### How to Contribute

We host our code on [Github](https://github.com/tarosky/taro-ad-fields), so feel free to send PR or issues.

### Is there any vulnerability?

As far as we konw, **NO**. But nothing is perfect.
This plugin allows you to save Javascript like Google Adsense code,
so please be careful about who can edit your ad.
You can customize the capability for ad fields by hooking `taf_post_type_args` filter.

## Screenshots

W.I.P

## Changelog

### 1.1.0

* Add iframe feature. Now you can provide ad field as iframe ad container. e.g. Facebook Instant Article.

### 1.0.0

* First Release.
