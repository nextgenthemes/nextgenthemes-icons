<?php

declare(strict_types=1);

use const Nextgenthemes\Icons\PLUGIN_DIR;

class Tests_Icon_Data extends WP_UnitTestCase {

	private static array $icons = [];

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$icons = require PLUGIN_DIR . '/src/bootstrap-icons-data.php';
	}

	public function test_icon_data_is_array(): void {
		$this->assertIsArray( self::$icons );
	}

	public function test_icon_count_above_2000(): void {
		$this->assertGreaterThan( 2000, self::$icons );
	}

	public function test_each_icon_has_title(): void {
		foreach ( self::$icons as $slug => $data ) {
			$this->assertArrayHasKey( 'title', $data, "Icon '$slug' missing title" );
			$this->assertNotEmpty( $data['title'], "Icon '$slug' has empty title" );
		}
	}

	public function test_each_icon_has_categories(): void {
		foreach ( self::$icons as $slug => $data ) {
			$this->assertArrayHasKey( 'categories', $data, "Icon '$slug' missing categories" );
			$this->assertIsArray( $data['categories'], "Icon '$slug' categories is not array" );
		}
	}

	public function test_each_icon_has_tags(): void {
		foreach ( self::$icons as $slug => $data ) {
			$this->assertArrayHasKey( 'tags', $data, "Icon '$slug' missing tags" );
			$this->assertIsArray( $data['tags'], "Icon '$slug' tags is not array" );
		}
	}

	public function test_icon_slugs_are_strings(): void {
		foreach ( self::$icons as $slug => $data ) {
			$this->assertTrue( is_int( $slug ) || is_string( $slug ), "Icon slug is neither int nor string" );
		}
	}

	/**
	 * @dataProvider data_known_icons
	 */
	public function test_known_icons_exist( string $slug, string $expected_title ): void {
		$this->assertArrayHasKey( $slug, self::$icons );
		$this->assertSame( $expected_title, self::$icons[ $slug ]['title'] );
	}

	public function data_known_icons(): array {
		return [
			'arrow-left'  => [ 'arrow-left', 'Arrow left' ],
			'gear'        => [ 'gear', 'Gear' ],
			'star'        => [ 'star', 'Star' ],
			'house'       => [ 'house', 'House' ],
			'search'      => [ 'search', 'Search' ],
		];
	}

	public function test_icon_slugs_match_svg_files(): void {
		$svg_dir = PLUGIN_DIR . '/public/icons/bootstrap';

		foreach ( self::$icons as $slug => $data ) {
			$svg_path = $svg_dir . '/' . $slug . '.svg';
			$this->assertFileExists( $svg_path, "SVG file missing for icon '$slug'" );
		}
	}
}
