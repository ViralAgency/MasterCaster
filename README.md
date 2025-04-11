# PHP MasterCaster

[![Latest Stable Version](https://poser.pugx.org/viral-agency/master-caster/v/stable)](https://packagist.org/packages/viral-agency/master-caster)
[![Total Downloads](https://poser.pugx.org/viral-agency/master-caster/downloads)](https://packagist.org/packages/viral-agency/master-caster)
[![License](https://poser.pugx.org/viral-agency/master-caster/license)](https://packagist.org/packages/viral-agency/master-caster)

Unlike other strongly typed languages (such as Java), PHP typically converts API requests from/to JSON using arrays.
The problem with this approach is that it is impossible to know upfront the number, names, or types of properties being converted. This leads to unpredictable scenarios and makes the code harder to maintain. As a result, developers are often forced into complex technical workarounds, which, over time, become unmanageable. This is especially true when dealing with deeply nested JSON properties, where developers are forced to write iterative loops, resulting in messy and poorly maintainable code.
MasterCaster bridges the gap between strong typing and PHP's flexibility. It allows you to define a model where the properties of objects are specified in advance, based on the third-party API documentation. This enables JSON responses to be dynamically and recursively converted into specific objects. At the same time, it lets you define custom methods within the classes, taking full advantage of OOP principles such as inheritance, encapsulation, and polymorphism.

In short, it’s a small revolution in PHP.

## Requirements
- PHP >= 8.0
- Composer (you can install from [here](https://getcomposer.org/download/))
- A well-written third-party API documentation (in order to fit the requirements out-of-the-box, the JSON representation of the response have to meet the [Google JSON style guide](https://google.github.io/styleguide/jsoncstyleguide.xml), in particularly way the [array naming convention](https://google.github.io/styleguide/jsoncstyleguide.xml?showone=Singular_vs_Plural_Property_Names#Singular_vs_Plural_Property_Names))

## Installation

To install the package, run the following command using Composer:

```bash
composer require viral-agency/master-caster
```

Alternatively, you can add the package to your `composer.json` file:

```json
"require": {
"viral-agency/master-caster": "^1.0.0"
}
```

Then, run:

```bash
composer install viral-agency/master-caster
```


## Usage

You can easily to use MasterCaster by defining a model from the third party API documentation:



### Sample third-party API documentation
Here a sample third party API documentation that fits the [Google JSON style guide](https://google.github.io/styleguide/jsoncstyleguide.xml

```json
{
  "sampleInt": 5,
  "sampleString": "sample_string",
  "sampleObject": {
    "sampleString": "sample_string",
    "sampleInt": 2
  },
  "sampleObjects": [
    {
      "sampleString": "sample_string",
      "sampleInt": 2
    },
    {
      "sampleString": "sample_string",
      "sampleInt": 2
    }
  ]
}
```
### Define the models
Define each elements of the JSON representation by coping directly from the documentation, in order to be sure that's correct.

#### Sample Response Class

```php
<?php

namespace SomeNameSpace;

use ViralAgency\MasterCaster;

class SampleResponse extends MasterCaster {
	
	public int $sampleInt;
	public string $sampleString;
	public SampleObject $sampleObject;
	public array $sampleObjectsM
}

```
#### Sample Object Class

Please note: you are free to put all the nested objects that you need. 

```php
<?php

namespace SomeNameSpace;

use ViralAgency\MasterCaster;

class SampleObject extends MasterCaster {
	
	public int $sampleInt;
	public string $sampleString;
	
}
```

Replace the name following the third-party documentation. 

### Features

- Inheritance: All the defined objects inherit the same constructor
- Array casting of objects: MasterCaster translate the array of objects in the model-defined objects, getting che class name by singularize the array name
- Names translations: MasterCaster translate automatically names from camelCase to PascalCase, in order to get the classnames correctly

## Troubleshooting

#### Array of objects name is not pluralized
Yeah. In some cases the array of objects is not pluralized. You are free to override the base method in order to match your name, by adding an intermediate class.
In my case, the "video" object array wasn't pluralized:

```php
<?php

namespace SomeNameSpace;

use ViralAgency\MasterCaster;

class CustomCaster extends MasterCaster {

    private function handleArrayValue(string $key, array $values): void
    {
        foreach ( $values as $value ) {
            //add here the cases
            if ($key === "video"){
                $this->$key[] = $this->buildObject( $key, $value, false);
            }
            //otherwise let's proceed in default way
            else {
                $this->key[] = is_object($values) ? $this->buildObject( $key, $value, true ) : $value;
            }   
        }
    }
}
```


## Contributing

Contributions are welcome! If you find problems, please report by open a support request, thanks

## Support

If you encounter issues, feel free to open an issue in
the [GitHub repository](https://github.com/ViralAgency/MasterCaster/issues).

## License

This package is open-sourced software licensed under the [Apache 2 License](https://www.apache.org/licenses/LICENSE-2.0).

---
