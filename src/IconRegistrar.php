<?php

declare(strict_types=1);

namespace Nextgenthemes\Icons;

final class IconRegistrar {

	private const BS_COLLECTION = 'bootstrap-icons';
	private const BS_ICONS_PATH = PLUGIN_DIR . '/public/icons/bootstrap';

	public function boot(): void {
		add_action('init', $this->register(...));
	}

	private function register(): void {
		$icons = require PLUGIN_DIR . '/src/bootstrap-icons-data.php';

		wp_register_icon_collection( self::BS_COLLECTION, [
			'label'       => __('Bootstrap', 'nextgenthemes-icons'),
			'description' => __('2,000+ Bootstrap Icons.', 'nextgenthemes-icons'),
		] );

		foreach ($icons as $slug => $data) {
			wp_register_icon( self::BS_COLLECTION . '/' . $slug, [
				'label'     => $data['title'],
				'file_path' => self::BS_ICONS_PATH . '/' . $slug . '.svg',
			] );
		}
	}
}
