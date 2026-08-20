<?php

declare(strict_types=1);

class Tests_Icon_Registration extends WP_UnitTestCase {

	public function test_bootstrap_collection_registered(): void {
		$registry = WP_Icon_Collections_Registry::get_instance();
		$collection = $registry->get_registered( 'bootstrap-icons' );

		$this->assertNotNull( $collection, 'bootstrap-icons collection not registered' );
		$this->assertSame( 'Bootstrap', $collection['label'] );
	}

	public function test_icons_registered_in_registry(): void {
		$registry = WP_Icons_Registry::get_instance();

		$icons = $registry->get_registered_icons( 'bootstrap-icons/' );
		$this->assertGreaterThan( 2000, count( $icons ) );
	}

	/**
	 * @dataProvider data_sample_icons
	 */
	public function test_specific_icon_registered( string $slug ): void {
		$registry = WP_Icons_Registry::get_instance();
		$name = 'bootstrap-icons/' . $slug;

		$this->assertTrue( $registry->is_registered( $name ), "Icon '$name' not registered" );
	}

	public function data_sample_icons(): array {
		return [
			'arrow-left'  => [ 'arrow-left' ],
			'gear'        => [ 'gear' ],
			'star'        => [ 'star' ],
			'house'       => [ 'house' ],
			'search'      => [ 'search' ],
			'heart'       => [ 'heart' ],
			'person'      => [ 'person' ],
			'x-circle'    => [ 'x-circle' ],
		];
	}

	public function test_registered_icon_has_label(): void {
		$registry = WP_Icons_Registry::get_instance();
		$icon = $registry->get_registered_icon( 'bootstrap-icons/gear' );

		$this->assertNotNull( $icon );
		$this->assertSame( 'Gear', $icon['label'] );
	}

	public function test_registered_icon_has_collection(): void {
		$registry = WP_Icons_Registry::get_instance();
		$icon = $registry->get_registered_icon( 'bootstrap-icons/gear' );

		$this->assertNotNull( $icon );
		$this->assertSame( 'bootstrap-icons', $icon['collection'] );
	}

	public function test_registered_icon_has_file_path(): void {
		$registry = WP_Icons_Registry::get_instance();
		$icon = $registry->get_registered_icon( 'bootstrap-icons/gear' );

		$this->assertNotNull( $icon );
		$this->assertArrayHasKey( 'file_path', $icon );
		$this->assertStringEndsWith( '/gear.svg', $icon['file_path'] );
	}

	public function test_icon_content_loads_svg(): void {
		$registry = WP_Icons_Registry::get_instance();
		$icon = $registry->get_registered_icon( 'bootstrap-icons/gear' );

		$this->assertNotNull( $icon );
		$this->assertStringStartsWith( '<svg', $icon['content'] );
		$this->assertStringContainsString( '</svg>', $icon['content'] );
	}
}
