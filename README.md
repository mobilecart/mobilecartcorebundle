# MobileCartCoreBundle

This is the core of Mobile Cart.

Mobile Cart is an E-Commerce Shopping Cart package built with PHP ; on the Symfony Framework .

Directions:

Install this bundle into Symfony 2.8 (add the bundle to app/AppKernel.php)

Run some console commands:

For a fresh install of Symfony 2.8:

./app/console doctrine:schema:drop --force

./app/console doctrine:schema:create

./app/console cart:init:itemvarsets

./app/console cart:ref:regions

./app/console cart:create:adminuser admin@fake.com passw0rd

./app/console cart:create:customer customer@fake.com passw0rd 


