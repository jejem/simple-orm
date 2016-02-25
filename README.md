# simple-orm
PHP class for an easy to use ORM

[![Latest Stable Version](https://poser.pugx.org/phyrexia/orm/v/stable)](https://packagist.org/packages/phyrexia/orm)
[![License](https://poser.pugx.org/phyrexia/orm/license)](https://packagist.org/packages/phyrexia/orm)

## Requirements

- PHP >= 5.3
- Composer [phyrexia/sql](https://packagist.org/packages/phyrexia/sql) ^1.0

## Installation

Install directly via [Composer](https://getcomposer.org):
```bash
$ composer require phyrexia/orm
```

## Basic Usage

```php
<?php
require 'vendor/autoload.php';

use Phyrexia\ORM\SimpleORM;

class User extends SimpleORM {
	protected static $table = 'user';

	public $id;

	public function __construct($id=NULL) {
		$this->id = $id;
	}
}

//Load User with ID 1
$user = User::load(1);

//Save User
$user->save();

//Delete User
$user->delete();

//Check if User with ID 1 exists
$exists = User::exists(1);

//Load all Users
$users = User::loadAll();
```
