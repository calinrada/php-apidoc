php-apidoc
==========

Generate documentation for php API based application. No dependency. No framework required.

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [Preview](#preview)

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
     * @ApiMethod(type="get", route="/user/get/{id}")
     * @ApiParams(name="id", type="integer", nullable=false, description="User id")
     */
    public function get()
    {
    
    }
    
    /**
     * @ApiDescription(section="User", description="Create's a new user")
     * @ApiMethod(type="post", route="/user/create")
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

try {
    $builder = new Builder($st_classes);
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




