<?php
	/**
	 * Author:      Kolja Nolte
	 * E-mail:      kolja.nolte@gmail.com
	 * Website:     www.koljanolte.com
	 * Created:     11.06.2015 at 01:44 GMT+7
	 */

	/**
	 * Loads .css and .js files.
	 *
	 * @since 1.0.0
	 */
	function thumbnail_cleaner_scripts_and_styles() {
		$root = plugin_dir_url(dirname(__FILE__));

		/** Styles */
		wp_enqueue_style("thumbnail-cleaner-styles-main", $root . "styles/main.css", array(), THUMBNAIL_CLEANER_VERSION);
		wp_enqueue_style("thumbnail-cleaner-font-awesome", "//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css", array(), THUMBNAIL_CLEANER_VERSION);

		/** Scripts */
		wp_enqueue_script("thumbnail-cleaner-scripts-main", $root . "scripts/main.js");
	}

	add_action("admin_enqueue_scripts", "thumbnail_cleaner_scripts_and_styles");

	/**
	 * Registers Thumbnail Cleaner's menu page.
	 *
	 * @since 1.0.0
	 */
	function thumbnail_cleaner_register_menu_page() {
		add_management_page(
			"Thumbnail Cleaner",
			"Thumbnail Cleaner",
			"manage_options",
			"thumbnail-cleaner",
			"thumbnail_cleaner_menu_page"
		);
	}

	add_action("admin_menu", "thumbnail_cleaner_register_menu_page");