<?php
/**
 * Plugin Name:       NGT Bootstrap Icons (2000+)
 * Plugin URI:        https://github.com/nextgenthemes/nextgenthemes-icons
 * Description:       Registers the Bootstrap Icons collection with WordPress.
 * Version:           1.0.0
 * Requires at least: 7.1
 * Requires PHP:      8.1
 * Author:            Nicolas Jonas
 * Author URI:        https://nextgenthemes.com
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       nextgenthemes-icons
 */

declare(strict_types=1);

namespace Nextgenthemes\Icons;

defined('ABSPATH') || exit;

const PLUGIN_DIR = __DIR__;

require_once PLUGIN_DIR . '/src/IconRegistrar.php';

add_action('plugins_loaded', static function (): void {
	(new IconRegistrar())->boot();
});
