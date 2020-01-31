# php-json-patch-generator

[![Latest Stable Version](https://poser.pugx.org/ridvanaltun/json-patch-generator/v/stable)](https://packagist.org/packages/ridvanaltun/json-patch-generator)
[![Total Downloads](https://poser.pugx.org/ridvanaltun/json-patch-generator/downloads)](https://packagist.org/packages/ridvanaltun/json-patch-generator)
[![Latest Unstable Version](https://poser.pugx.org/ridvanaltun/json-patch-generator/v/unstable)](https://packagist.org/packages/ridvanaltun/json-patch-generator)
[![License](https://poser.pugx.org/ridvanaltun/json-patch-generator/license)](https://packagist.org/packages/ridvanaltun/json-patch-generator)
[![composer.lock](https://poser.pugx.org/ridvanaltun/json-patch-generator/composerlock)](https://packagist.org/packages/ridvanaltun/json-patch-generator)

> Generate JSON Patch (IETF RFC-6902).

This library allows you generate json-patch in PHP.

## Installation

```bash
$ composer require ridvanaltun/json-patch-generator
```

## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use ridvanaltun\JsonPatchGenerator\Utils;

$utils = new Utils();

$oldSnap = [
  'name'    => 'foo',
  'surname' => 'bar',
  'skils'   => [
    'computer_science' => true,
    'algorithm'        => true,
    'math'             => false,
  ],
  'specs'   => [
    'a',
    'b',
    'c',
  ]
];

$currSnap = [
  'name'  => 'foo',
  'age'   => 23,
  'skils' => [
    'computer_science' => true,
    'algorithm'        => false,
  ],
  'specs' => [
    'a',
    'b',
    'd',
    'e',
  ]
];

$jsonPatch = $utils->generateJsonPatch($currSnap, $oldSnap);

var_dump($jsonPatch);
```
**OUTPUT:**
```
array(7) {
  [0]=>
  array(3) {
    ["op"]=>
    string(3) "add"
    ["path"]=>
    string(4) "/age"
    ["value"]=>
    int(23)
  }
  [1]=>
  array(3) {
    ["op"]=>
    string(7) "replace"
    ["path"]=>
    string(16) "/skils/algorithm"
    ["value"]=>
    bool(false)
  }
  [2]=>
  array(3) {
    ["op"]=>
    string(3) "add"
    ["path"]=>
    string(6) "/specs"
    ["value"]=>
    string(1) "d"
  }
  [3]=>
  array(3) {
    ["op"]=>
    string(3) "add"
    ["path"]=>
    string(6) "/specs"
    ["value"]=>
    string(1) "e"
  }
  [4]=>
  array(2) {
    ["op"]=>
    string(6) "remove"
    ["path"]=>
    string(8) "/surname"
  }
  [5]=>
  array(2) {
    ["op"]=>
    string(6) "remove"
    ["path"]=>
    string(11) "/skils/math"
  }
  [6]=>
  array(3) {
    ["op"]=>
    string(6) "remove"
    ["path"]=>
    string(6) "/specs"
    ["value"]=>
    string(1) "c"
  }
}
```
