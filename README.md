# MobileCartCoreBundle

This is the core of Mobile Cart.

Mobile Cart is an E-Commerce Shopping Cart package built with PHP ; on the Symfony Framework .

The Admin Theme is here:

https://github.com/mobilecart/mobilecartadminbundle

The Default Frontend Theme is here:

https://github.com/mobilecart/mobilecartfrontendbundle

Directions:

After a fresh install of Symfony 2.8:

Now, we can install Mobile Cart

$ composer require mobilecart/corebundle:dev-master

Install this bundle into Symfony 2.8 (add the bundle to app/AppKernel.php)

$bundles = array(

...

new MobileCart\CoreBundle\MobileCartCoreBundle(),

);

Run some console commands:

$ ./app/console doctrine:schema:create

$ ./app/console cart:init:itemvarsets

$ ./app/console cart:ref:regions

$ cp vendor/mobilecart/corebundle/Resources/config/security.yml ./app/config/

$ cp vendor/mobilecart/corebundle/Resources/config/routing.yml ./app/config/

$ ./app/console cart:create:adminuser admin@fake.com passw0rd

$ ./app/console cart:create:customer customer@fake.com passw0rd

Next, we can install the Admin and Frontend themes

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

Note: if you receive a MySQL error which looks like: "ORDER BY clause is not in SELECT list,
references column X which is not in SELECT list; this is incompatible with DISTINCT" .. ,
it is because the Paginator in Doctrine 2.5.x does not yet handle this "bug" which is specific to MySQL 5.7


