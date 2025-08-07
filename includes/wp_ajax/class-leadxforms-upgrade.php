<?php

if (!class_exists('UpgradePluginVersion')) {
    class UpgradePluginVersion
    {
        public static function upgradeVersion()
        {
            $plugin_dir = WP_PLUGIN_DIR;
            $prefix = 'leadxforms-v';
            $current_plugin_file = plugin_dir_path(__DIR__) . 'leadxforms.php';
            $current_plugin_data = get_plugin_data($current_plugin_file);
            $current_version = $current_plugin_data['Version'];
            $current_plugin_path = realpath(dirname($current_plugin_file));

            $folders = scandir($plugin_dir);
            foreach ($folders as $folder) {
                if ($folder === '.' || $folder === '..') {
                    continue;
                }

                $full_path = $plugin_dir . '/' . $folder;
                $plugin_file = $full_path . '/leadxforms.php';

                if (!is_dir($full_path) || !file_exists($plugin_file)) {
                    continue;
                }

                if (realpath($full_path) === $current_plugin_path) {
                    continue; // Skip current version
                }

                if (strpos($folder, $prefix) === 0) {
                    $old_version_data = get_plugin_data($plugin_file);
                    $old_version = $old_version_data['Version'];

                    if (version_compare($current_version, $old_version, '>')) {
                        $plugin_slug = $folder . '/leadxforms.php';
                        deactivate_plugins($plugin_slug);

                        // Schedule deletion after everything else
                        add_action('shutdown', function () use ($full_path) {
                            self::recursive_delete_folder($full_path);
                        });
                    }
                }
            }
        }
        public static function recursive_delete_folder($folder_path)
        {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            rmdir($folder_path);
        }
    }
}