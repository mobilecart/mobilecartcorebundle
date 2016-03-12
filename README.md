# MobileCartCoreBundle

This is the core of Mobile Cart.

Mobile Cart is an E-Commerce Shopping Cart package built with PHP ; on the Symfony Framework .

The Admin Theme is here:

https://github.com/mobilecart/mobilecartadminbundle

The Default Frontend Theme is here:

https://github.com/mobilecart/mobilecartfrontendbundle

Directions:

First step: remove Doctrine 2.5.x and install Doctrine 2.4.x

$ composer require doctrine/common:2.4.*

$ composer require doctrine/dbal:2.4.*

$ composer require doctrine/orm:2.4.*

$ composer require mobilecart/corebundle:dev-master

Install this bundle into Symfony 2.8 (add the bundle to app/AppKernel.php)

$bundles = array(

...

new MobileCart\CoreBundle\MobileCartCoreBundle(),

);

Run some console commands:

For a fresh install of Symfony 2.8:

$ ./app/console doctrine:schema:create

$ ./app/console cart:init:itemvarsets

$ ./app/console cart:ref:regions

$ cp vendor/mobilecart/corebundle/Resources/config/security.yml ./app/config/

$ cp vendor/mobilecart/corebundle/Resources/config/routing.yml ./app/config/

$ ./app/console cart:create:adminuser admin@fake.com passw0rd

$ ./app/console cart:create:customer customer@fake.com passw0rd

$ composer require mobilecart/adminbundle:dev-master

$ composer require mobilecart/frontendbundle:dev-master

Install these bundles into Symfony 2.8 (add the bundle to app/AppKernel.php)

$bundles = array(

...

new MobileCart\CoreBundle\MobileCartAdminBundle(),
new MobileCart\CoreBundle\MobileCartFrontendBundle(),

);

$ ./app/console assets:install --symlink

...

Note: if you receive a MySQL error which looks like: "ORDER BY clause is not in SELECT list, references column X which is not in SELECT list; this is incompatible with DISTINCT" .. , it is because Doctrine 2.5.x does not yet handle this edge case; specific to MySQL 5.7


