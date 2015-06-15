<?php
	/**
	 * Plugin Name:  Thumbnail Cleaner
	 * Plugin URI:   http://www.koljanolte.com/wordpress/plugins/thumbnail-cleaner/
	 * Description:  Cleans up your WordPress installation by removing and regenerating thumbnails.
	 * Version:      1.0.0
	 * Author:       Kolja Nolte
	 * Author URI:   http://www.koljanolte.com
	 * License:      GPLv2 or later
	 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
	 */

	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	define("THUMBNAIL_CLEANER_VERSION", "1.0");

	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	/**
	 * Includes all files from the "includes" directory.
	 */
	$include_files = glob(dirname(__FILE__) . "/includes/*.php");
	foreach($include_files as $include_file) {
		include($include_file);
	}

	/**
	 * Loads translations.
	 *
	 * @since 1.0.0
	 */
	function thumbnail_cleaner_translations() {
		load_plugin_textdomain("thumbnail_cleaner", false, dirname(plugin_basename(__FILE__)) . "/languages/");
	}

	add_action("plugins_loaded", "thumbnail_cleaner_translations");