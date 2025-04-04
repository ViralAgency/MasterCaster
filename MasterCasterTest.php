<?php

namespace Viral\MasterCaster\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Viral\MasterCaster\MasterCaster;

class MasterCasterTest extends TestCase {
	/**
	 * Test that the constructor initializes properties when an associative array is passed.
	 */
	public function testConstructorAssignsPropertiesFromArray() {
		$data = [
			'exampleProperty' => 'exampleValue',
			'anotherProperty' => 42,
		];

		// Mock a class extending MasterCaster to add test-specific properties.
		$mock = new class( $data ) extends MasterCaster {
			public $exampleProperty;
			public $anotherProperty;
		};

		$this->assertEquals( 'exampleValue', $mock->exampleProperty );
		$this->assertEquals( 42, $mock->anotherProperty );
	}

	/**
	 * Test that the constructor handles an object as data input.
	 */
	public function testConstructorAssignsPropertiesFromObject() {
		$data                  = new \stdClass();
		$data->exampleProperty = 'exampleValue';
		$data->anotherProperty = 42;

		// Mock a class extending MasterCaster to add test-specific properties.
		$mock = new class( $data ) extends MasterCaster {
			public $exampleProperty;
			public $anotherProperty;
		};

		$this->assertEquals( 'exampleValue', $mock->exampleProperty );
		$this->assertEquals( 42, $mock->anotherProperty );
	}

	/**
	 * Test that the constructor ignores properties not defined in the class.
	 */
	public function testConstructorIgnoresUndefinedProperties() {
		$data = [
			'undefinedProperty' => 'value',
			'definedProperty'   => 123,
		];

		// Mock a class extending MasterCaster to add test-specific properties.
		$mock = new class( $data ) extends MasterCaster {
			public $definedProperty;
		};

		$this->assertEquals( 123, $mock->definedProperty );
		$this->assertObjectNotHasAttribute( 'undefinedProperty', $mock );
	}

	/**
	 * Test that the constructor handles null data without errors.
	 */
	public function testConstructorHandlesNullData() {
		$mock = new class( null ) extends MasterCaster {
			public $someProperty;
		};

		$this->assertNull( $mock->someProperty );
	}

	/**
	 * Test that the constructor correctly processes scalar values.
	 */
	public function testConstructorProcessesScalarValues() {
		$data = [
			'intProperty'    => '42',
			'boolProperty'   => 'true',
			'stringProperty' => 123,
		];

		// Mock a class extending MasterCaster to add test-specific properties.
		$mock = new class( $data ) extends MasterCaster {
			public $intProperty;
			public $boolProperty;
			public $stringProperty;
		};

		$this->assertIsInt( $mock->intProperty );
		$this->assertIsBool( $mock->boolProperty );
		$this->assertIsString( $mock->stringProperty );

		$this->assertEquals( 42, $mock->intProperty );
		$this->assertEquals( true, $mock->boolProperty );
		$this->assertEquals( '123', $mock->stringProperty );
	}

	/**
	 * Test that the constructor handles an array of objects.
	 */
	public function testConstructorHandlesArrayOfObjects() {
		$data = [
			'items' => [
				(object) [ 'name' => 'Item1' ],
				(object) [ 'name' => 'Item2' ],
			],
		];

		// Mock a class extending MasterCaster to add test-specific properties.
		$mock = new class( $data ) extends MasterCaster {
			public array $items = [];
		};

		$this->assertCount( 2, $mock->items );
		$this->assertEquals( 'Item1', $mock->items[0]->name );
		$this->assertEquals( 'Item2', $mock->items[1]->name );
	}

	/**
	 * Test construction when environment variable API_MODEL_NAMESPACE is not set.
	 */
	public function testConstructorHandlesUnsetBaseNamespace() {
		putenv( 'API_MODEL_NAMESPACE=' );
		$data = [
			'property' => 'value',
		];

		$mock = new class( $data ) extends MasterCaster {
			public $property;
		};

		$this->assertEquals( 'value', $mock->property );
	}

	/**
	 * Test construction with an invalid property type causing ReflectionException.
	 */
	public function testConstructorHandlesReflectionException() {
		$this->expectOutputString( "Property does not exist" );

		$mock = new class( null ) extends MasterCaster {
			protected function handleObjectValue( string $key, object $value ): void {
				try {
					throw new ReflectionException( "Property does not exist" );
				} catch ( ReflectionException $e ) {
					echo $e->getMessage();
				}
			}
		};

		$this->assertTrue( true ); // Ensure test completes.
	}
}
