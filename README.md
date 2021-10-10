# JsonSerialize

![PSR12 checks](https://github.com/andreamk/JsonSerialize/actions/workflows/phpcs.yml/badge.svg) ![PHPUnit checks](https://github.com/andreamk/JsonSerialize/actions/workflows/phpunit.yml/badge.svg) 

This library combines the features of the native PHP serialization with the JSON portability, in particular it allows to encode with JSON also **protected and private properties** of an object.
When defined in classes, the magic methods [__sleep](https://www.php.net/manual/en/language.oop5.magic.php#object.sleep) and [__wakeup](https://www.php.net/manual/en/language.oop5.magic.php#object.wakeup) are used in the same way as they are used in serialization.

Values serialized and unserialized with this library retain their type, so arrays, associative arrays, and objects will retain their type and class.

### Requirements
PHP 5.4+
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

### AbstractJsonSerializable class

```PHP
class MyClass extends \Amk\JsonSerialize\AbstractJsonSerializable {

}

$obj  = new MyClass();
$json = json_encode($obj);
```
Extending the **AbstractJsonSerializable** class that implements the [JsonSerializable interface](https://www.php.net/manual/en/class.jsonserializable.php) allows to use the normal json_encode function of PHP obtaining for the object that extends this class the same result that you would get using *JsonSerialize::serialize*

### Flag JSON_SERIALIZE_SKIP_CLASS_NAME

In some circumstances it can be useful to serialize an object in JSON without exposing the class. For example if we want to send the contents of an object to a browser via an AJAX call.
In these cases we can use the JSON_SERIALIZE_SKIP_CLASS_NAME flag in addition to the normal flags of the json_encode function.

```PHP
use Amk\JsonSerialize\JsonSerialize;

$json = JsonSerialize::serialize(
    $value,
    JSON_PRETTY_PRINT | JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME
);
```

### Method unserializeToObj

```PHP
public static JsonSerialize::unserializeToObj(
    string $json, 
    object $obj, 
    $depth = 512, 
    $flags = 0
) : object
```

In some circumstances it may be useful to unserialize JSON data in an already instantiated object. For example if we are working on a serialized JSON with the JSON_SERIALIZE_SKIP_CLASS_NAME flag.

In this case we don't have the information about the reference class so using the normal unserialize function the result would be an associative array. Using unserializeToObj method we force the use of the object passed by parameter.

```PHP
$obj = new MyClass();
$json = JsonSerialize::serialize($obj , JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);


$obj2 = new MyClass();
JsonSerialize::unserializeToObj($json, $obj2);
```

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