# DoctrineLogBundle
Symfony bundle to enable auto logging data changes. Works with doctrine entities only. This bundle will save developer's ass to determine how data has been changed :)

Often projects need to log change to see the complete history of changes are made on an object. It's mainly for superadmin who needs to know these information. To control it from a central place and log automatically this bundle is developed. Using this bundle in your symfony project you can choose which entities you want to autolog. This bundle will automatically insert a record each time the data is changed. You can configure which property you want to track with 2 strategy. 

This bundle is tested on symfony 4

Prerequisities
--------------

This bundle use `stof/doctrine-extensions-bundle` to use blameable and timestampable. Install the bundle first and configure to use blamable and timestampable. Read the installation guide here https://symfony.com/doc/master/bundles/StofDoctrineExtensionsBundle/index.html

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

Add `@Loggable` annotation in the entity you want to log changes. By default strategy will be `include_all`, all properties changes will be logged.

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

#### Strategy
There are two strategies. `exclude_all` & `include_all`. Exclude all will automatically skip all properties. And icclude all will automatically log all properties.

Strategies are used only for update. Create and Delete actions always logged.

If you want to exclude all properties and only log one property then use strategy `exclude_all` and use `@Log` annotation only in the property you want to log.

```php
// src/Entity/User.php

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mb\DoctrineLogBundle\Annotation\Log;
use Mb\DoctrineLogBundle\Annotation\Loggable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 *
 * @Loggable(strategy="exclude_all")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", column="name")
     * @Log
     */
    protected $name;

    /**
     * @ORM\Column(type="text", column="user_name")
     */
    protected $username;

}
```

Above example will only log for the `$name` property.

If you want to log all but skip one property. Then use strategy `include_all` and in the property use annotation `@Exclude` to skip that property.

```php
// src/Entity/User.php

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mb\DoctrineLogBundle\Annotation\Exclude;
use Mb\DoctrineLogBundle\Annotation\Loggable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 *
 * @Loggable(strategy="include_all")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", column="name")
     * @Exclude
     */
    protected $name;

    /**
     * @ORM\Column(type="text", column="user_name")
     */
    protected $username;

}
```

That's it now it'll start auto logging entity changes and store inside the `mb_entity_log` table.

Configuration
-------------

If you are using blameable trait or timestampable trait then you must want to log changes of those properties. Or you may have some common properties that is added in many entries and you don't want to log the changes of those properties then add this configuration.
A different entity manager can be configured to support multiple entity managers. The listener service could be extended to make any required changes.

```yaml
# config/packages/mb_doctrine_log.yaml

mb_doctrine_log:
  ignore_properties:
    - createdBy
    - updatedBy
    - createAt
    - updatedAt
  entity_manager: 'default'
  listener_class: 'Mb\DoctrineLogBundle\EventListener\Logger'
```

Any property name you configure here, if the Loggable entity has that property will be ignored.

How data will be saved
----------------------

In the `mb_entity_log` table there are 9 columns

 1. id (primary key)
 2. object_class (the class being changed)
 3. foreign_key (the id of the object)
 4. action (create|update|delete)
 5. changes (serialized changes, keys are property name, and value is an array, 1st element prev val, 2nd element new val)
 6. created_by (the user to blame)
 7. updated_by (the user to blame)
 8. created_at (timestamp)
 9. updated_at

Now you can choose how to display this data to super admin.
