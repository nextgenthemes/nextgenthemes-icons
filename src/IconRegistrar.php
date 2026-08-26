<?php

declare(strict_types = 1);

namespace Nextgenthemes\Icons;

final class IconRegistrar {

	private const ICONS_DIR = PLUGIN_DIR . '/public/icons';
	private const DATA_DIR  = PLUGIN_DIR . '/src/data';

	public function boot(): void {
		add_action( 'init', $this->register( ... ) );
	}

	private static function get_collections(): array {

		// NOTE: Collections commented out below lose >70% of their icons to the
		// WP_Icons_Registry SVG sanitizer allowlist (see tests/issues.md and
		// https://core.trac.wordpress.org/ticket/65795).
		// Uncomment as soon as WordPress sanitizes these tags/attributes:
		// circle/ellipse/g/line/polyline/rect + stroke*/opacity/fill-opacity.
		return [
			'bootstrap' => [
				'label'       => __( 'Bootstrap', 'nextgenthemes-icons' ),
				'description' => __( '2,000+ Bootstrap Icons.', 'nextgenthemes-icons' ),
				'path'        => self::ICONS_DIR . '/bootstrap',
			],
			// 'phosphor-thin'     => [
			//  'label' => __( 'Phosphor Thin', 'nextgenthemes-icons' ),
			//  'path'  => self::ICONS_DIR . '/phosphor/svgs/thin',
			// ],
			// 'phosphor-light'    => [
			//  'label' => __( 'Phosphor Light', 'nextgenthemes-icons' ),
			//  'path'  => self::ICONS_DIR . '/phosphor/svgs/light',
			// ],
			// 'phosphor-regular'  => [
			//  'label' => __( 'Phosphor Regular', 'nextgenthemes-icons' ),
			//  'path'  => self::ICONS_DIR . '/phosphor/svgs/regular',
			// ],
			'phosphor-fill' => [
				'label' => __( 'Phosphor Fill', 'nextgenthemes-icons' ),
				'path'  => self::ICONS_DIR . '/phosphor/svgs/fill',
			],
			// 'phosphor-bold'     => [
			//  'label' => __( 'Phosphor Bold', 'nextgenthemes-icons' ),
			//  'path'  => self::ICONS_DIR . '/phosphor/svgs/bold',
			// ],
			// 'phosphor-duotone'  => [
			//  'label' => __( 'Phosphor Duotone', 'nextgenthemes-icons' ),
			//  'path'  => self::ICONS_DIR . '/phosphor/svgs/duotone',
			// ],
			'phosphorflat-thin'    => [
				'label' => __( 'Phosphor Flat Thin', 'nextgenthemes-icons' ),
				'path'  => self::ICONS_DIR . '/phosphor/svgs-flat/thin',
			],
			'phosphorflat-light'   => [
				'label' => __( 'Phosphor Flat Light', 'nextgenthemes-icons' ),
				'path'  => self::ICONS_DIR . '/phosphor/svgs-flat/light',
			],
			'phosphorflat-regular' => [
				'label' => __( 'Phosphor Flat Regular', 'nextgenthemes-icons' ),
				'path'  => self::ICONS_DIR . '/phosphor/svgs-flat/regular',
			],
			'phosphorflat-fill'    => [
				'label' => __( 'Phosphor Flat Fill', 'nextgenthemes-icons' ),
				'path'  => self::ICONS_DIR . '/phosphor/svgs-flat/fill',
			],
			'phosphorflat-bold'    => [
				'label' => __( 'Phosphor Flat Bold', 'nextgenthemes-icons' ),
				'path'  => self::ICONS_DIR . '/phosphor/svgs-flat/bold',
			],
			// 'phosphorflat-duotone' => [
			//  'label' => __( 'Phosphor Flat Duotone', 'nextgenthemes-icons' ),
			//  'path'  => self::ICONS_DIR . '/phosphor/svgs-flat/duotone',
			// ],
		];
	}
	private function register(): void {

		$phosphor = null;

		foreach ( self::get_collections() as $collection_slug => $collection_args ) {

			wp_register_icon_collection(
				$collection_slug,
				[
					'label'       => $collection_args['label'],
					'description' => $collection_args['description'] ?? '',
				]
			);

			if ( str_starts_with( $collection_slug, 'phosphor' ) ) {

				// Special case: all phosphor collections live in one data file,
				// keyed by collection slug.
				$phosphor ??= require self::DATA_DIR . '/phosphor.php';
				$icons      = $phosphor[ $collection_slug ] ?? array();

				if ( empty( $icons ) ) {
					wp_trigger_error(
						__METHOD__,
						sprintf(
							'No icons found for collection "%s" in phosphor.php. Re-run bin/compile-phosphor.nu.',
							$collection_slug
						)
					);
				}
			} else {
				// Default: one flat data file per collection, like bootstrap.
				$icons = require self::DATA_DIR . "/{$collection_slug}.php";
			}

			foreach ( $icons as $slug => $data ) {
				wp_register_icon(
					"$collection_slug/$slug",
					[
						'label'     => $data['title'],
						'file_path' => $collection_args['path'] . "/$slug.svg",
					]
				);
			}
		}
	}
}
