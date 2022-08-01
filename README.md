# JsonSerialize

[![PHPStan](https://github.com/andreamk/JsonSerialize/actions/workflows/phpstan.yml/badge.svg)](https://github.com/andreamk/JsonSerialize/actions/workflows/phpstan.yml) [![PSR12](https://github.com/andreamk/JsonSerialize/actions/workflows/phpcs.yml/badge.svg)](https://github.com/andreamk/JsonSerialize/actions/workflows/phpcs.yml) [![PHPUnit 5.4,5.6](https://github.com/andreamk/JsonSerialize/actions/workflows/phpunit_php5.yml/badge.svg)](https://github.com/andreamk/JsonSerialize/actions/workflows/phpunit_php5.yml) [![PHPUnit 7, 8](https://github.com/andreamk/JsonSerialize/actions/workflows/phpunit.yml/badge.svg)](https://github.com/andreamk/JsonSerialize/actions/workflows/phpunit.yml)

This library combines the features of the native PHP serialization with the JSON portability, in particular it allows to encode with JSON also **protected and private properties** of an object.
When defined in classes, the magic methods [__sleep](https://www.php.net/manual/en/language.oop5.magic.php#object.sleep) , [__serialize](https://www.php.net/manual/en/language.oop5.magic.php#object.serialize), [__wakeup](https://www.php.net/manual/en/language.oop5.magic.php#object.wakeup) and [__unserialize](https://www.php.net/manual/en/language.oop5.magic.php#object.unserialize) are used in the same way as they are used in serialization.

Values serialized and unserialized with this library retain their type, so arrays, associative arrays, and objects will retain their type and class.

### Requirements
PHP 5.4+

### Installation

Via Composer

```
composer require andreamk/jsonserialize 
```

It's possibile include the library either using the composer autoloader or using the library autoloader

```PHP
require_once PATH . '/jsonserialize/src/Autoloader.php';
\Amk\JsonSerialize\Autoloader::register();
```


## Basic usage

```PHP
use Amk\JsonSerialize\JsonSerialize;

$json = JsonSerialize::serialize($value);

$value = JsonSerialize::unserialize($json);
```
---

```PHP
public static JsonSerialize::serialize(mixed $value, int $flags = 0, int $depth = 512): string|false
```

The serialize function converts any value in JSON like the [json_encode](https://www.php.net/manual/en/function.json-encode.php) function with the difference that in the JSON will be present also the private and protected properties of the objects in addition to the reference to the corresponding class.

```PHP
public static JsonSerialize::unserialize(string $json, int $depth = 512, int $flags = 0): mixed
```

Takes a JSON encoded string and converts it into a PHP variable like [json_decode](https://www.php.net/manual/en/function.json-decode.php). In case the class to which the object refers is not defined then the object will be instantiated as stdClass and all properties become public.

Note: When an object is unserialized it is instantiated without calling the constructor exactly like the unserialize function of PHP.  If the variable being unserialized is an object, after successfully reconstructing the object PHP will automatically attempt to call __wakeup() method (if exists).
## Advanced usage
### Method __sleep

```PHP
public __sleep(): array
```

Amk/JsonSerialize serialize functions checks if the class has a function with the magic name __sleep(). If so, that function is executed prior to any serialization. It can clean up the object and is supposed to return an array with the names of all variables of that object that should be serialized. If the method doesn't return an array an exception is thrown.

The intended use of __sleep() is to commit pending data or perform similar cleanup tasks. Also, the function is useful if a very large objects doesn't need to be saved completely.

### Method __wakeup

```PHP
public __wakeup(): void
```

Amk/JsonSerialize unserialize functions checks for the presence of a function with the magic name __wakeup(). If present, this function can reconstruct any resources that the object may have.

The intended use of __wakeup() is to reestablish any database connections that may have been lost during serialization and perform other reinitialization tasks. 


### Method __serialize

```PHP
public __serialize(): array
```

Amk/JsonSerialize serialize functions checks if the class has a function with the magic name __serialize(). If so, that function is executed prior to any serialization. It must construct and return an associative array of key/value pairs that represent the serialized form of the object. If no array is returned a Exception will be thrown.

 *Note: If both __serialize() and __sleep() are defined in the same object, only __serialize() will be called. __sleep() will be ignored.*

### Method __unserialize

```PHP
public __unserialize(array $data): void
```
Amk/JsonSerialize unserialize functions checks for the presence of a function with the magic name __unserialize(). If present, this function will be passed the restored array that was returned from __serialize(). It may then restore the properties of the object from that array as appropriate.

*Note: If both __unserialize() and __wakeup() are defined in the same object, only __unserialize() will be called. __wakeup() will be ignored.*

### AbstractJsonSerializable class

```PHP
class MyClass extends \Amk\JsonSerialize\AbstractJsonSerializable {
}

$obj  = new MyClass();
$json = json_encode($obj);
```
Extending the **AbstractJsonSerializable** class that implements the [JsonSerializable interface](https://www.php.net/manual/en/class.jsonserializable.php) allows to use the normal json_encode function of PHP obtaining for the object that extends this class the same result that you would get using *JsonSerialize::serialize*

### Flags
#### - JSON_SKIP_CLASS_NAME

In some circumstances it can be useful to serialize an object in JSON without exposing the class. For example if we want to send the contents of an object to a browser via an AJAX call.
In these cases we can use the JSON_SKIP_CLASS_NAME flag in addition to the normal flags of the json_encode function.

```PHP
use Amk\JsonSerialize\JsonSerialize;

$json = JsonSerialize::serialize(
    $value,
    JSON_PRETTY_PRINT | JsonSerialize::JSON_SKIP_CLASS_NAME
);
```
#### - JSON_SKIP_MAGIC_METHODS

When this flag is on, the __sleep and __serialize methods of the class are ignored.
#### - JSON_SKIP_SANITIZE

By default serialization applied string sanitization in case json_encode fails due to invalid characters.
Activating this flag turns sanitization off.

### Method serializeToData

```PHP
public static serializeToData(
    mixed $value, 
    $flags = 0
): mixed
```

In some circumstances, it is useful to be able to process the data structure before transforming it into JSON.
Using the serializeToData method, you get the value that would be passed to the json_encode function with the serialize method

```PHP
$data = JsonSerialize::serializeToData($obj, JsonSerialize::JSON_SKIP_CLASS_NAME);
$data['extraProp'] = true;
unset($data['prop']);
$json = json_encode($data);
```

### Method unserializeToObj

```PHP
public static JsonSerialize::unserializeToObj(
    string $json, 
    object|string $obj, 
    int $depth = 512, 
    int $flags = 0
) : object
```

In some circumstances it may be useful to unserialize JSON data in an already instantiated object. For example if we are working on a serialized JSON with the JSON_SKIP_CLASS_NAME flag.

In this case we don't have the information about the reference class so using the normal unserialize function the result would be an associative array. Using unserializeToObj method we force the use of the object passed by parameter.

```PHP
$obj = new MyClass();
$json = JsonSerialize::serialize($obj , JsonSerialize::JSON_SKIP_CLASS_NAME);


$obj2 = new MyClass();
JsonSerialize::unserializeToObj($json, $obj2);
```

### Method unserializeWithMap

```PHP
public static unserializeWithMap(
    string $json, 
    JsonUnserializeMap $map, 
    $depth = 512, 
    $flags = 0
): mixed
```

Deserialization with mapping is a very powerful method of mapping properties to change the type of in deserialization.
The classic use is to force object deserialization of what would normally be an associative array. 
In addition to nuti PHP native types there are special types in which you can indicate the class of an object and also reference another proprity for offects with recursive references

```PHP
$map = new JsonUnserializeMap(
    [
        '' => 'object';
        'prop1/prop11' => 'bool',
        'prop2' => 'cl:MyClass'
    ]
);
$val = JsonSerialize::unserializeWithMap($json, $map);
```

See the [Mapping section](#unserialize-mapping) for more information


## Unserialize Mapping

The mapping is defined through the `JsonUnserializeMap` class.
It can be initialized by passing an array of items to the constructor, and the list of items can be manipulated later with the methods 
`addProp`, `removeProp`, `resetMap`

A mapping object consists of the key (property identifier) value (property type) pair.
**Please note that a mapping does not require the definition of all the properties of a structure but only the properties that need to be forced to a specific type**

```PHP
$map = new JsonUnserializeMap(
    [
    'prop' => 'object',
    'prop/flag1' => 'int',
    'prop/flag2' => 'bool'
    ]
);
```

### Property identifier

- the empty identifier corresponds to the root element
- Property levels are separated by the character `/`
- The `*` character is the wildcard character, useful if you want to map all the elements of an array

Some examples
```PHP
[
    '' => type, // itendifies the root element
    'prop' => type, // identifies the level 1 property ($val->prop or $val['prop'])
    'v1/v2/v3' => type, // identifies the level 3 property ($val->v1->v2->v3 or $val['v1']['v2']['v3'])
    'v1/*' => type, // identifies all the properties that are children of v1,
    'v1/*/v3' => type, // identifies all v3 properties of the children of v1
]
```

- The wildcard property but less priority than a specific property so you can define a type for all but a few properties. in the next example all child properties of element will be integers except flag which will be boolean

```PHP
[
    'element/*' => 'int',
    'element/flag' => 'bool',
]
```

### Property type

- The type is a string that can be a php native type `bool`, `boolean`, `float` , `int` , `integer` , `string` , `array` , `object` , `null`.
- If the type starts with the character `?` then it can be nullable. This means that the json value is null is kept null otherwise it takes the value of the type. 
- With the special type `cl:` followed by the class identifier, it is defined that the type is the object of the class defined
- With the special type `rf:` followed by the property identifier without a wildcard, a reference to the property is defined. This only makes sense if the property is already defined and is an object

```PHP
$map = new JsonUnserializeMap(
    [
    '' => 'cl:' . MyClass::class, // root val is an istance of MyClass
    'items/*' => '?cl:' . MyItems::class, // all element of items are istances of MyItems or null
    'items/*/parent' => 'rf:', // all prop parent of all items ar a reference to root value
    'obj' => '?object' // obj can be null or an object
    ]
);

```

notes:
When defining a class type `cl:` it is initialized by also executing the __wakeup and unserialize methods if they exist
When defining a reference `rf:` all child values of this object in the json are ignored and since the class is already initialized no __wakeup or __unserialie methods are executed

## How works

The serialize and unserialize methods work on standard JSON and can be read by any function that writes/reads json files. In particular if value is a scalar value or an array there is no difference in the result using standard functions json_encode and json_decode.

If you use these functions with objects, in addition to the public and private properties, the class to which the data belongs is also added.


This code
```PHP
namespace Test;

class MyClass {
    public $publicProp = 'public';
    protected $protectedProp = 'protected';
    private $privateProp = 'private';
    private $arrayProp = [1,2,3];
}

$object = new MyClass();
$json = JsonSerialize::serialize($object, JSON_PRETTY_PRINT);
```

It will generate this JSON
```JSON
{
    "CL_-=_-=": "Tests\\MyClass",
    "publicProp": "public",
    "protectedProp": "protected",
    "privateProp": "private",
    "arrayProp": [
        1,
        2,
        3
    ]
}
```
When deserializing a json if the data is of type array and the key AbstractJsonSerializeObjData::CLASS_KEY_FOR_JSON_SERIALIZE (CL_-=_-=) is present this array is converted into an object.

If the class exists the instantiated object will belong to the defined class, otherwise it will be an stdClass object.

In case the object is of type stdClass then all items of the array will become public properties otherwise every property defined in the class and present in the array will be updated with the value of the array.

It is very important to understand the difference in this functionality if you work with projects that save serialized classes that change over time.

Suppose we have a Wordpress plugin with a Settings class that describes the settings of our plugin. It is very likely that over time properties of this class will be added or removed as the plugin evolves. In this case we can have serialized properties that do not exist in the class and properties that exist in the class but have not been serialized.

Deserialization handles this by discarding all properties that are in the JSON but not defined in the class and leaving the default of properties that are defined in the class but do not exist in the JSON, unless it is the stdClass class where all JSON values are assigned.

So in the case we have this json
```JSON
{
    "CL_-=_-=": "MyClass",
    "propA": "value A",
    "propB": "value B"
}
```
and class defined in this way 
```PHP
class MyClass {
    public $propA = 'init A';
    public $propC = 'init C';
}

$obj = JsonSerialize::unserialize($json);
var_dump($obj);
```
the result will be 
```
object(MyClass)#1 (2) {
  ["propA"]=>
  string(7) "value A"
  ["propC"]=>
  string(6) "init C"
}
```