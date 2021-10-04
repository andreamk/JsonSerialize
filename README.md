# JsonSerialize

This library combines the features of the native PHP serialization with the JSON portability, in particular it allows to encode in the JSON also protected and private properties of an object.

## Basic usage

```PHP
$json = JsonSerialize::serialize($value);

$value = JsonSerialize::unserialize($json);
```

The serialize and unserialize methods work on standard JSON and can be read by any function that writes/reads json files. In particular if value is a scalar value or an array there is no difference in the result using standard functions json_encode and json_decode.

If you use these functions with objects, in addition to the public and private properties, the class to which the data belongs is also added.


This code
```PHP
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
    "==_CL_==_==": "Amk\\JsonSerialize\\Tests\\MyClass",
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

Which if decoded by the method **JsonSerialize::unserialize**  will instantiate the same object.

| First Header  | Second Header |
| ------------- | ------------- |
| ```PHP |  aaa |
|   if (true) | aaa |
| ```  | Content Cell  |
| Content Cell  | Content Cell  |