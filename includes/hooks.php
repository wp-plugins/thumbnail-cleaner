<?php
	/**
	 * Author:      Kolja Nolte
	 * E-mail:      kolja.nolte@gmail.com
	 * Website:     www.koljanolte.com
	 * Created:     11.06.2015 at 01:44 GMT+7
	 */

	/**
	 * Loads translation file.
	 */
	function thumbnail_cleaner_translations() {
		$translation_path = plugin_dir_path(__FILE__) . "../languages/" . get_site_option("WPLANG") . ".mo";
		load_textdomain("thumbnail_cleaner", $translation_path);
	}

	add_action("plugins_loaded", "thumbnail_cleaner_translations");

	/**
	 * Loads .css and .js files.
	 *
	 * @since 1.0.0
	 */
	function thumbnail_cleaner_scripts_and_styles() {
		$root = plugin_dir_url(dirname(__FILE__));

		/** Styles */
		wp_enqueue_style("thumbnail-cleaner-styles-main", "$root/styles/main.css", array(), THUMBNAIL_CLEANER_VERSION);
		wp_enqueue_style("thumbnail-cleaner-font-awesome", "$root/fonts/font-awesome/css/font-awesome.min.css", array(), THUMBNAIL_CLEANER_VERSION);

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