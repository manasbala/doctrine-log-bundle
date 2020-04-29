# DoctrineLogBundle
Symfony bundle to enable auto logging doctrine entity changes.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require manasbala/doctrine-log-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require manasbala/doctrine-log-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Mb\DoctrineLogBundle\MbDoctrineLogBundle::class => ['all' => true],
];
```

### Step 3: Update database

It'll create the `mb_entity_log` table in your database

```console
$ php bin/console doctrine:schema:update --force
```

### Step 4: Configure entities you need to log

Add `@Loggable` annotation in the entity you want to log changes.

```php
// src/Entity/User.php

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mb\DoctrineLogBundle\Annotation\Loggable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 *
 * @Loggable
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
```

That's it now it'll start auto logging entity changes and store inside the `mb_entity_log` table.

Configuration
-------------

If you are using blameable trait or timestampable trait then you must want to log changes of those properties. Or you may have some common properties that is added in many entries and you don't want to log the changes of those properties then add this configuration.

```yaml
//config/packages/mb_doctrine_log.yaml

mb_doctrine_log:
  ignore_properties:
    - createdBy
    - updatedBy
    - createAt
    - updatedAt

```
