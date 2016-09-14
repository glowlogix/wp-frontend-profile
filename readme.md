# WP Front End Profile

Contributors: wpmarkuk<br />
Donate link: http://markwilkinson.me/saythanks<br />
Tags: profile, users, user meta<br />
Requires at least: 4.0.1<br />
Tested up to: 4.0.1<br />
Stable tag: 0.2.2<br />
License: GPLv2 or later<br />
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Front End Profile allows users to edit their profile without going into the dashboard to do so.

## Description

WP Front End Profile give you the ability to add a extensible user profile section to the front end of your WordPress website. By default the plugin adds two tabs to the front end profile. One of these titled profile allows a user to edit their user data including email, first and last names, URL and bio (description). The password tab allows a user to change their password for the site.

### Plugin Extensibility

As the front end profile is rendered with tabs you can easily add your own tabs with your own fields to store user meta data. Tabs and fields are added through filters and all the saving of the data is taken care of for you.

You can add the following field types:

*	WYSIWYG
*	Select
* Text Area
*	Checkbox
*	Password
*	Email
*	Text

See FAQs for how to add our own fields and tabs.

### Profile Output

To output the front end profile you can use the following function in your template files:

```
wpfep_show_profile();
```

## Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php wpfep_show_profile(); ?>` in your templates where you want the front end profile to display

## Frequently Asked Questions

### How do I add my own tab to the profile output?

Tabs can be added using the `wpfep_tabs` filter provided. Below is an example of how to add a tab after the default Profile and Password tabs.

```php
<?php
function wpmark_add_tab( $tabs ) {
	
	/* add our tab to the tabs array */
	$tabs[] = array(
		'id' => 'wpmark_tab',
		'label' => 'Testing',
		'tab_class' => 'testing-tab',
		'content_class' => 'testing-content',
	);
	
	/* return all the tabs */
	return $tabs;
	
}

add_filter( 'wpfep_tabs', 'wpmark_add_tab', 30 );
?>
```

Note here the priority of 30 which means after the Profile tab (10) and the Password tab (20).

### How do I add fields to tab?

Fields can be added to a tab using a dynamic filter named `wpfep_fields_$tab_id`. The tab ID is the id of tab as declared when adding the tab (see FAQ above). This means that you can add fields to any tab even the default tabs. Below is an example of how you would add fields to a tab with the ID of `wpmark_tab`:

```php
<?php
function wpmark_add_tab_fields( $fields ) {
	
	$fields[] = array(
		'id' => 'testing_field',
		'label' => 'Testing',
		'desc' => 'Just testing.',
		'type' => 'text',
		'classes' => 'testing',
	);
	
	return $fields;

}

add_filter( 'wpfep_fields_wpmark_tab', 'wpmark_add_tab_fields', 10 );
?>
```

### Are there any field IDs I cannot use?

Yes there are two field IDs reserved which are `user_email` and `user_url`. This is because you should not save new meta data with these keys are they already exist, but not in the `user_meta` table.

## Changelog

### 0.2.2
* Security: Fix privilege escalation and XSS vulnerabilities.

### 0.2.1
* Added ability to display form via shortcode.

### 0.2
* Include a nonce in the front end editing form for security.

### 0.1
* Initial launch.
