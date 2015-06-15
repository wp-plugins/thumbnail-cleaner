<?php
	/**
	 * Author:      Kolja Nolte
	 * E-mail:      kolja.nolte@gmail.com
	 * Website:     www.koljanolte.com
	 * Created:     11.06.2015 at 01:46 GMT+7
	 */

	/**
	 * Stop script when the file is called directly.
	 */
	if(!function_exists("add_action")) {
		return false;
	}

	/**
	 * Thumbnail Cleaner menu page.
	 *
	 * @since 1.0.0
	 */
	function thumbnail_cleaner_menu_page() {
		$success        = false;
		$message        = "";
		$menu_page_url  = get_admin_url(0, "tools.php?page=thumbnail-cleaner");
		$analyze_output = "";
		$actions        = array(
			"backup",
			"delete_backups",
			"analyze",
			"clean_thumbnails",
			"regenerate_thumbnails",
			"restore"
		);

		if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"], $_GET["_wpnonce"])) {
			foreach((array)$actions as $action) {
				if($_GET["action"] != str_replace("_", "-", $action) || !wp_verify_nonce($_GET["_wpnonce"], "thumbnail_cleaner_$action")) {
					continue;
				}

				$thumbnail_cleaner = new Thumbnail_Cleaner();

				if($action == "backup") {
					$backup = $thumbnail_cleaner->backup();
					if($backup) {
						$success  = true;
						$message  = sprintf(__("The backup <strong>%s</strong> has been successfully created in <code>wp-content/backups/thumbnail-cleaner/</code>. <a href=\"%s\">Click here</a> to download the archive.", "thumbnail_cleaner"), $backup["file_name"], $backup["url"]);
						$timezone = get_option("timezone_string");
						if($timezone && strstr($timezone, "/")) {
							date_default_timezone_set($timezone);
						}
						update_option("thumbnail_cleaner_last_backup_date", date("Y-m-d H:i:s"));
					}
				}
				elseif($action == "delete_backups") {
					$deleted_backups = $thumbnail_cleaner->delete_backups();
					if($deleted_backups) {
						$success = true;
						$message = sprintf(__("All <strong>%s backups</strong> have been successfully deleted.", "thumbnail_cleaner"), $deleted_backups);
					}
				}
				elseif($action == "analyze") {
					$output = $thumbnail_cleaner->analyze();
					if($output) {
						$success        = true;
						$message        = __("Your <code>uploads</code> directory has been successfully analyzed. See the report below.", "thumbnail_cleaner");
						$analyze_output = $output;
					}
				}
				elseif($action == "clean_thumbnails") {
					$cleaned_thumbnails = $thumbnail_cleaner->clean_thumbnails();
					if($cleaned_thumbnails) {
						$success = true;
						$message = _n(__("Thumbnail Cleaner found and deleted <strong>1 thumbnail</strong>.", "thumbnail_cleaner"), sprintf(__("Thumbnail Cleaner found and deleted <strong>%s thumbnails</strong>.", "thumbnail_cleaner"), $cleaned_thumbnails), $cleaned_thumbnails, "thumbnail_cleaner");
					}
					else {
						$message = sprintf(__("No thumbnails found or thumbnails could not be deleted. Please see the <a href=\"%s\" target=\"_blank\">FAQ</a> for a list of possible causes and how to fix them.", "thumbnail_cleaner"), "http://www.koljanolte.com/wordpress/plugins/thumbnail-cleaner/#FAQ");
					}
				}
				elseif($action == "regenerate_thumbnails") {
					echo "xxx";
				}
				elseif($action == "restore" && isset($_GET["file_name"])) {
					if($thumbnail_cleaner->restore($_GET["file_name"])) {
						$success = true;
						$message = sprintf(__("The backup <strong>%s</strong> has been successfully restored.", "thumbnail_cleaner"), $_GET["file_name"]);
					}
				}
			}
		}
		?>
		<div class="wrap thumbnail-cleaner" id="thumbnail-cleaner-menu-page">
			<h2>
				<i class="fa fa-picture-o"></i>
				<?php echo get_admin_page_title(); ?>
			</h2>
			<?php
				if($success) {
					?>
					<div class="updated">
						<p><?php echo $message; ?></p>
					</div>
				<?php
				}
				elseif($message) {
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
				<?php
				}
			?>
			<div class="intro">
				<p><?php _e("Welcome to <strong>Thumbnail Cleaner</strong>. This plugin will clean up your WordPress installation by removing and regenerating thumbnails from your webserver while keeping the original uploaded images.", "thumbnail_cleaner"); ?></p>
				<p><?php _e("<strong>Important:</strong> To make sure you don't lose any data, please hit the \"Backup Now\" button. A zipped archive of your entire <code>uploads</code> directory will then be saved into <code>wp-content/backups/thumbnail-cleaner/</code> from where you can restore the previous version at any time.", "thumbnail_cleaner"); ?></p>
				<p><?php echo sprintf(__("For further information, please see the <a href=\"%s\" target=\"_blank\">official documentation</a>.", "thumbnail_cleaner"), "http://www.koljanolte.com/wordpress/plugins/thumbnail-cleaner/"); ?></p>
			</div>

			<table class="form-table">
				<tr>
					<th>
						<label for="backup"><?php _e("Backup", "thumbnail_cleaner"); ?></label>
					</th>
					<td>
						<?php
							if(extension_loaded("zip")) {
								?>
								<a href="<?php echo wp_nonce_url($menu_page_url . "&action=backup", "thumbnail_cleaner_backup"); ?>" class="button-primary">
									<i class="fa fa-floppy-o"></i>
									<?php _e("Backup Now", "thumbnail_cleaner"); ?>
								</a>
								<?php
								$disabled            = "";
								$backups_count       = thumbnail_cleaner_get_backups();
								$deleted_backups_url = wp_nonce_url($menu_page_url . "&action=delete-backups", "thumbnail_cleaner_delete_backups");
								$has_backups         = "true";
								if(!$backups_count) {
									$backups_count       = array();
									$deleted_backups_url = "#";
									$disabled            = " disabled";
									$has_backups         = "false";
								}
								?>
								<input type="hidden" id="has-backups" value="<?php echo $has_backups; ?>"/>
								<input type="hidden" id="has-backups-text" value="<?php echo __("There is currently no backup of your uploads directory. Are you sure you want to continue?", "thumbnail_cleaner") ?>"/>
								<a href="<?php echo $deleted_backups_url; ?>" class="button"<?php echo $disabled; ?>>
									<i class="fa fa-trash-o"></i>
									<?php echo _n("Delete 1 Backup", sprintf("Delete %s Backups", count($backups_count)), count($backups_count), "thumbnail_cleaner"); ?>
								</a>
								<span class="last-backup"><strong><?php _e("Last backup", "thumbnail_cleaner"); ?>:</strong> <?php thumbnail_cleaner_the_last_backup_date("F j, Y H:i"); ?></span>
								<p class="description">
									<?php _e("Creates a zipped archive of your <code>uploads</code> directory and saves it in <code>wp-content/backups/thumbnail-cleaner/</code> (recommended).", "thumbnail_cleaner"); ?>
								</p>
							<?php
							}
							else {
								_e("The Zip PHP extension is not activated on your server. Backups are not possible unless you contact your server provider and ask them to enable this feature.", "thumbnail_cleaner");
							}
						?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="analyze"><?php _e("Analyze", "thumbnail_cleaner"); ?></label>
					</th>
					<td>
						<a href="<?php echo wp_nonce_url($menu_page_url . "&action=analyze", "thumbnail_cleaner_analyze"); ?>" class="button-primary">
							<i class="fa fa-bar-chart"></i>
							<?php _e("Start Analyzing", "thumbnail_cleaner"); ?>
						</a>
						<p class="description"><?php _e("Scans your <code>uploads</code> directory for thumbnails and returns the total amount without deleting any files. ", "thumbnail_cleaner"); ?></p>
						<?php
							if($analyze_output) {
								?>
								<p class="analyze-output">
									<strong><?php _e("Found:", "thumbnail_cleaner"); ?></strong>
									<?php echo sprintf(__("%s original image(s) and %s thumbnail(s).", "thumbnail_cleaner"), count($analyze_output["original"]), count($analyze_output["thumbnails"])); ?>
								</p>
							<?php
							}
						?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="clean-thumbnails"><?php _e("Clean Thumbnails", "thumbnail_cleaner"); ?></label>
					</th>
					<td>
						<a href="<?php echo wp_nonce_url($menu_page_url . "&action=clean-thumbnails", "thumbnail_cleaner_clean_thumbnails"); ?>" class="button-primary" id="clean-thumbnails">
							<i class="fa fa-picture-o"></i>
							<?php
								if($analyze_output) {
									echo sprintf(__("Delete %s Thumbnail(s)", "thumbnail_cleaner"), count($analyze_output["thumbnails"]));
								}
								else {
									_e("Delete Thumbnails", "thumbnail_cleaner");
								}
							?>
						</a>
						<p class="description"><?php _e("Deletes <strong>all</strong> thumbnails within your <code>uploads</code> directory. <strong>Note:</strong> Unless you have created a backup, this <strong>cannot</strong> be reversed.", "thumbnail_cleaner"); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="regenerate-thumbnails"><?php _e("Regenerate Thumbnails", "thumbnail_cleaner"); ?></label>
					</th>
					<td>
						<?php
							$url = "#";
							if(!class_exists("RegenerateThumbnails")) {
								if(is_dir(ABSPATH . "wp-content/plugins/regenerate-thumbnails")) {
									$plugin_status = "inactive";
								}
								else {
									$plugin_status = "not_exists";
								}
							}
							else {
								$plugin_status = "active";
								$url           = get_admin_url() . "tools.php?page=regenerate-thumbnails";
							}

							$disabled = "";
							if($plugin_status != "active") {
								$disabled = " disabled";
							}

						?>
						<a href="<?php echo $url; ?>" class="button-primary" id="regenerate-thumbnails"<?php echo $disabled; ?>>
							<i class="fa fa-cogs"></i>
							<?php _e("Regenerate Now", "thumbnail_cleaner"); ?>
						</a>
						<?php
							if($plugin_status == "inactive") {
								echo sprintf(__("The plugin <strong>Regenerate Thumbnails</strong> is inactive. Please <a href=\"%s\" target=\"_blank\">enable</a>.", "thumbnail_cleaner"), get_admin_url() . "plugins.php#regenerate-thumbnails");
							}
							elseif($plugin_status == "not_exists") {
								echo sprintf(__("The plugin <strong>Regenerate Thumbnails</strong> is not installed. Please <a href=\"%s\" target=\"_blank\">download</a>.", "thumbnail_cleaner"), get_admin_url() . "plugin-install.php?tab=plugin-information&plugin=regenerate-thumbnails");
							}
						?>
						<p class="description">
							<?php echo sprintf(__("Recreates thumbnails for uploaded images. Check your <a href=\"%s\" target=\"_blank\">Media</a> page to change the resolution of the thumbnails.", "thumbnail_cleaner"), get_admin_url() . "options-media.php"); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="restore"><?php _e("Restore Backup", "thumbnail_cleaner"); ?></label>
					</th>
					<td>
						<?php
							$backups = thumbnail_cleaner_get_backups();
							if($backups) {
								?>
								<form action="<?php echo $menu_page_url; ?>" method="get">
									<label for="restore"><?php _e("Select backup to restore:", "thumbnail_cleaner"); ?> </label>
									<input type="hidden" name="page" value="thumbnail-cleaner"/>
									<input type="hidden" name="action" value="restore"/>
									<select name="file_name" id="restore">
										<?php
											foreach((array)$backups as $backup) {
												?>
												<option value="<?php echo $backup["file_name"]; ?>">
													<?php echo $backup["file_name"]; ?>
												</option>
											<?php
											}
										?>
									</select>
									<input type="submit" class="button" value="<?php _e("Restore", "thumbnail_cleaner"); ?>"/>
									<?php wp_nonce_field("thumbnail_cleaner_restore"); ?>
								</form>
								<p class="description"><?php _e("Replaces your <code>uploads</code> directory with a previously backed up version.", "thumbnail_cleaner"); ?></p>
							<?php
							}
							else {
								_e("No backups found.", "thumbnail_cleaner");
							}
						?>
					</td>
				</tr>
			</table>
		</div>
	<?php
	}