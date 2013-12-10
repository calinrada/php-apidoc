php-apidoc
==========

Generate documentation for php API based application. No dependency. No framework required.

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [Preview](#preview)
* [Tips](#tips)
* [Known issues](#known-issues)
* [TODO](#todo)

### <a id="requirements"></a>Requirements

PHP >= 5.3.2

### <a id="installation"></a>Installation

The recommended installation is via compososer. Just add the following line to your composer.json:

```json
{
    "crada/php-apidoc": "@dev"
}
```

```bash
$ php composer.phar update
```
### <a id="usage"></a>Usage

```php
class User
{
    /**
     * @ApiDescription(section="User", description="Get information about user")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/user/get/{id}")
     *
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     * @ApiParams(name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}")
     */
    public function get()
    {

    }

    /**
     * @ApiDescription(section="User", description="Create's a new user")
     * @ApiMethod(type="post")
     * @ApiRoute(name="/user/create")
     *
     * @ApiParams(name="username", type="string", nullable=false, description="Username")
     * @ApiParams(name="email", type="string", nullable=false, description="Email")
     * @ApiParams(name="password", type="string", nullable=false, description="Password")
     * @ApiParams(name="age", type="integer", nullable=true, description="Age")
     */
    public function create()
    {

    }
}
```

Create a file apidoc.php in your project root folder and add this content:


```php

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

$st_classes = array(
    'Application\Api\MyClass',
    'Application\Api\MyOtherClass',
);

$s_output_dir = __DIR__.'/apidocs';

try {
    $builder = new Builder($st_classes, $s_output_dir);
    $builder->generate();
} catch (Exception $e) {
    echo "There was an error generating the documentation: ", $e->getMessage();
}

```

Then, execute it via CLI

```php
$ php apidoc.php
```

### <a id="preview"></a>Preview

You can see a dummy generated documentation on http://calinrada.github.io/php-apidoc/

### <a id="tips"></a>Tips

To generate complex object sample input, use the ApiParam named "data":

```php
* @ApiParams(name="data", type="object", sample="{'user_id':'int','profile':{'email':'string','age':'integer'}}")
```

### <a id="knownissues"></a>Known issues

I don't know any, but please tell me if you find something. PS: I have tested it only in Chrome !

### <a id="todo"></a>TODO

* Implemend "add headers" functionality for sandbox
* Implement options for JSONP
* Implement "add fields" option

