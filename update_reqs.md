Plugin upgrade
To make later releases of your homemade WordPress plugin act as upgrades (rather than separate plugins), follow these best practices:

• Keep the plugin's directory and main file name the same across all versions. WordPress identifies plugins by their folder and main PHP file. If you change these, WordPress will treat the new version as a different plugin instead of an update[3][7][9].
• Update the plugin version number in your plugin's main file header (the comment block at the top) for each new release. This helps WordPress recognize that an update is available.
• Distribute the new version as a ZIP file with the same folder and main file name. When users upload the new ZIP via Plugins → Add New → Upload, WordPress will prompt to "Replace current with uploaded," upgrading the plugin in place without losing settings or data[3].
• Handle database or settings upgrades by:
• Storing your plugin's version in the WordPress options table (e.g., with update_option('my_plugin_version', 'x.y.z') ).
• On plugin load (e.g., in a function hooked to plugins_loaded ), compare the stored version with the new version using version_compare() .
• If the stored version is older, run any necessary upgrade routines, then update the stored version[1][2].

Example upgrade check in your main plugin file:

define('MY_PLUGIN_VERSION', '2.0.0');

function my_plugin_upgrade_check() {
    $version = get_option('my_plugin_version');
    if (version_compare($version, MY_PLUGIN_VERSION, '<')) {
        // Run upgrade routines here
        update_option('my_plugin_version', MY_PLUGIN_VERSION);
    }
}
add_action('plugins_loaded', 'my_plugin_upgrade_check');


This ensures seamless upgrades for users and preserves plugin data/settings between versions[1][2].

Summary Table

| What to Keep Consistent | What to Update Each Release |
| --- | --- |
| Plugin folder name | Version in main file header |
| Main PHP file name | Plugin code and upgrade routines |

If you follow these steps, users will be able to upgrade your plugin just like any standard WordPress plugin, rather than installing it as a new one[1][3][7][9].

Sources
[1] Wordpress plugin development: Versions and updates https://eggplantstudios.ca/2014/wordpress-plugin-development-versions-updates/
[2] Wordpress Update Plugin Hook/Action? Since 3.9 https://wordpress.stackexchange.com/questions/144870/wordpress-update-plugin-hook-action-since-3-9
[3] How to Manually Upgrade the Plugin Without Losing Existing Work https://www.wonderplugin.com/wordpress-carousel-plugin/how-to-upgrade-to-a-new-version-without-losing-existing-work/
[4] Leapfrogging multiple versions of WordPress - Wendy Cholbi https://www.wendycholbi.com/leapfrogging-multiple-versions-of-wordpress/
[5] Upgrading WordPress – Advanced Administration Handbook https://developer.wordpress.org/advanced-administration/upgrade/upgrading/
[6] Upgrading WordPress – Extended Instructions – Forum Italiano https://it.wordpress.org/support/article/upgrading-wordpress-extended-instructions/
[7] How to Properly Update WordPress Plugins (Step by Step) https://www.wpbeginner.com/beginners-guide/how-to-properly-update-wordpress-plugins-step-by-step/
[8] How To Downgrade WordPress To The Previous Version https://wpdeveloper.com/downgrade-wordpress/
[9] I Tested Every WordPress Version Control Plugin So You Don't ... https://duplicator.com/wordpress-version-control-plugin/
[10] How to update WordPress safely? [Complete guide] - WPMarmite https://wpmarmite.com/en/update-wordpress/