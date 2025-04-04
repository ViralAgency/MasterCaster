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

class MasterCaster {
	private static string $BASE_NAMESPACE;

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

	private function handleArrayValue(string $key, array $values): void
	{
		$inflector = InflectorFactory::create()->build();
		$key = $inflector->pluralize($key);
		foreach ($values as $propertyValue) {
			$this->{$key}[] = is_object($propertyValue) ? $this->classBuilder($key, $propertyValue, true) : $propertyValue;
		}
	}

	private function handleObjectValue(string $key, object $value): void
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

	private function handleScalarValue(string $key, mixed $value): void
	{
		$this->{$key} = is_numeric( $value ) ? (int) $value : ( is_bool( $value ) ? (bool) $value : (string) $value );
	}

	private function classBuilder(string $key, object $values, bool $isPlural = false)
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

	private function convertToPascalCase(string $value): string
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
