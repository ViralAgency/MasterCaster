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

/**
 * Class MasterCaster
 *
 * Provides functionality for constructing and initializing instances with dynamic properties
 * based on given data, processing different data types, and handling conversions for properties.
 * Includes support for converting arrays, objects, and scalar values and determining class types
 * dynamically for building complex object structures.
 */
class MasterCaster {
	private static string $BASE_NAMESPACE;

	/**
	 * Constructs a new instance and initializes object properties based on the provided data.
	 *
	 * @param object|array|null $data Optional data to initialize the object. It can be an object or an associative array where keys match the class properties. Any values provided will be processed and assigned to the corresponding properties.
	 *
	 * @return void
	 */
	public function __construct(object|array $data = null)
	{
		self::$BASE_NAMESPACE = getenv('API_MODEL_NAMESPACE');


		if ($data !== null) {
			foreach ($data as $key => $value) {
				if (!property_exists($this, $key)) {
					continue;
				}
				if (is_array($value)) {
					$this->handleArrayValue($key, $value);
				}
				elseif (is_object($value)) {
					$this->handleObjectValue($key, $value);
				}
				else {
					$this->handleScalarValue($key, $value);
				}
			}
		}
	}

	/**
	 * Handles the processing of array values by pluralizing the key, iterating through the values,
	 * and appropriately assigning them to the corresponding property.
	 *
	 * @param string $key The key representing the property to be populated.
	 * @param array $values An array of values to be processed and assigned.
	 *
	 * @return void
	 */
	protected function handleArrayValue(string $key, array $values): void
	{
		$inflector = InflectorFactory::create()->build();
		$key = $inflector->pluralize($key);
		foreach ($values as $propertyValue) {
			$this->{$key}[] = is_object($propertyValue) ? $this->classBuilder($key, $propertyValue, true) : $propertyValue;
		}
	}

	/**
	 * Handles the processing and assignment of an object value to the corresponding property
	 * based on its type as defined in the class reflection.
	 *
	 * @param string $key The key representing the property to be processed.
	 * @param object $value The object to be assigned to the property.
	 *
	 * @return void
	 */
	protected function handleObjectValue(string $key, object $value): void
	{
		$reflection = new ReflectionClass($this);
		try {
			$property = $reflection->getProperty($key);
			$propertyName = $property->getName();
			$propertyType = $property->getType()->getName();
		}
		catch (\ReflectionException $e) {
			echo $e->getMessage();
			return;
		}
		if ($propertyType === 'object') {
			$stdClass = new \stdClass();
			$stdClass = $value;
			$this->{$propertyName} = $stdClass;
		}
		else {
			$this->{$propertyName} = new $propertyType($value);

		}
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
	protected function handleScalarValue(string $key, mixed $value): void
	{
		$this->{$key} = is_numeric( $value ) ? (int) $value : ( is_bool( $value ) ? (bool) $value : (string) $value );
	}


	/**
	 * Constructs and returns an instance of a class based on the provided key and values.
	 *
	 * @param string $key The key used to determine the class name. The key may be converted to PascalCase and optionally singularized.
	 * @param object $values An object containing the values to pass to the class constructor.
	 * @param bool $isPlural Optional. Indicates whether the key should be singularized (default: false).
	 *
	 * @return object Either a new instance of the determined class with the provided values or the original values object if the class does not exist.
	 */
	protected function classBuilder(string $key, object $values, bool $isPlural = false)
	{

		$inflector = InflectorFactory::create()->build();
		$name = $this->convertToPascalCase($key);
		$name = $isPlural ? $inflector->singularize($name) : $name;

		if (class_exists(self::$BASE_NAMESPACE . $name)) {
			$classPathName = self::$BASE_NAMESPACE . $name;
			return new $classPathName($values);
		}

		return $values;
	}

	/**
	 * Converts a given string to PascalCase.
	 *
	 * @param string $value The input string to be converted. If the string contains underscores, it will be split into parts, and each part will be capitalized and concatenated.
	 *
	 * @return string The converted string in PascalCase format.
	 */
	protected function convertToPascalCase(string $value): string
	{
		if (str_contains($value, "_")) {
			$parts = array_map('ucfirst', explode("_", $value));
			return implode("", $parts);
		}

		return ucfirst($value);
	}

	public function toJson( $options = 0 ): string
	{
		return json_encode($this);
	}
}
