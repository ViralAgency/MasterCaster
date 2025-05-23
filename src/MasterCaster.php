<?php
/**
 * Copyright 2025 Luca Pisoni - Viral Agency
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ViralAgency\MasterCaster;

use Doctrine\Inflector\InflectorFactory;
use ReflectionClass;
use ReflectionException;

/**
 * Class MasterCaster
 *
 * Provides functionality for constructing and initializing instances with dynamic properties
 * based on given data, processing different data types, and handling conversions for properties.
 * Includes support for converting arrays, objects, and scalar values and determining class types
 * dynamically for building complex object structures.
 */
class MasterCaster {


	/**
	 * Constructor method for initializing properties of the class with provided data.
	 *
	 * @param object|array|null $data An optional parameter containing data used to initialize class properties.
	 * If provided, keys in the data must match class properties to be set. Supported value types include arrays, objects, and scalar values.
	 *
	 * @return void
	 */
	public function __construct( object|array $data = null ) {

		if ( $data !== null ) {
			foreach ( $data as $key => $value ) {
				if ( !property_exists( $this, $key ) ) {
					continue;
				}
				if ( is_array( $value ) ) {
					$this->handleArrayValue( $key, $value );
				} elseif ( is_object( $value ) ) {
					$this->handleObjectValue( $key, $value );
				} else {
					$this->handleScalarValue( $key, $value );
				}
			}
		}
	}


	/**
	 * Handles the processing of an array of values associated with a specified key.
	 *
	 * @param string $key The key associated with the array of values.
	 * @param array $values The array of values to be processed. Each value will either be added directly or processed into an object if applicable.
	 *
	 * @return void This method does not return a value.
	 */
	protected function handleArrayValue( string $key, array $values ): void
	{
		foreach ( $values as $value ) {

			$this->$key[] = is_object($value) ? $this->buildObject( $key, $value, true ) : $value;
		}
	}

	/**
	 * Handles the assignment of an object value to a designated property by processing it through the buildObject method.
	 *
	 * @param string $key The property name where the processed object will be assigned.
	 * @param object $value The object to process and assign to the specified property.
	 *
	 * @return void
	 */
	protected function handleObjectValue( string $key, object $value)
	{
		$this->$key = $this->buildObject($key, $value);
	}


	/**
	 * Builds and returns an object based on the provided key, value, and isPlural flag.
	 *
	 * @param string $key The property key used to determine the class name or retrieve the property type.
	 * @param object $value The value to initialize the created object, expected to be an object.
	 * @param bool $isPlural Optional flag indicating whether the key should be treated as plural and singularized. Default is false.
	 *
	 * @return object The newly instantiated object based on the key, value, and classpath. If no appropriate class is found, a generic stdClass is returned.
	 *
	 * @throws ReflectionException If an error occurs while trying to retrieve the property type using reflection.
	 */
	protected function buildObject( string $key, object $value, bool $isPlural = false ) : object
	{
		$inflector = InflectorFactory::create()->build();
		$reflection = new ReflectionClass( $this );

		//Singularize the name is if plural and create the classpath
		if ( $isPlural ) {
			$name     = $inflector->singularize($key);
			$className       = $inflector->classify($name);
			$namespace       = $reflection->getNamespaceName() . '\\';
			$classPathName   = $namespace . $className;
		}
		else {
			try {
				$classPathName = $reflection->getProperty($key)->getType()->getName();
			}
			catch ( ReflectionException $e ) {
				echo $e->getMessage();
				throw $e;
			}
		}

		//Get the class name from the prperty name

		$test = class_exists($classPathName);

		//If the class exist or is array return the object
		if (class_exists( $classPathName )) {
			$object = new $classPathName($value);
		}
		//otherwise is a generic stdClass
		else {
			$stdClass     = new \stdClass();
			$object     = $value;
		}
		return $object;
	}

	/**
	 * Handles the processing of scalar values by determining their type and assigning them
	 * as either an integer, boolean, or string to the corresponding property.
	 *
	 * @param string $key The key representing the property to be populated.
	 * @param mixed $value The scalar value to be processed and assigned.
	 *
	 * @return void
	 */
	protected function handleScalarValue( string $key, mixed $value ): void {
		$this->$key = is_numeric( $value ) ? (int) $value : ( is_bool( $value ) ? (bool) $value : (string) $value );
	}

	/**
	 * Converts the current object to its JSON representation.
	 *
	 * @param int $options Optional flags for JSON encoding. These options are constants defined in the PHP core JSON extension.
	 *
	 * @return string A JSON-encoded string representing the current object.
	 */
	public function toJson( $options = 0 ): string {
		return json_encode( $this );
	}
}
