# Taro Ad Fields

Contributors: Takahashi_Fumiki, tarosky  
Tags: advertisement  
Requires at least: 4.7.0
Tested up to: 4.8.1
Stable tag: 1.0.0
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Advertise block manager for WordPress.

## Description

You can create ad slot for you WordPress theme.
In each position, the latest ad field will be displayed.

### From Theme

Call action hook in your theme.

<pre>
<?php do_action( 'taro_ad_field', 'after-header', '<div class="after-header">', '</div>' ); ?>
</pre>

#### Arguments

1. Hook name.
2. Postion slug. 
3. String to be output just before ad block.
4. String to be output just after ad block.

### From Widget

We have widget for ad field. The latest ad of specified postion will be displayed.

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
		],
	];
} );
</pre>


## Installation

1. Upload the plugin files to the `/wp-content/plugins/tscf` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. If JSON doesn't exist, put it or create from `Appearance > Custom Fields Config`


## Frequently Asked Questions

### A question that someone might have

An answer to that question.


## Screenshots

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

## Changelog

### 1.0

* A change since the previous version.
* Another change.
