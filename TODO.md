# From Alpha to Beta:

* better Mass Import and Export functions
* some Unit Tests
* finish Discount handling and Admin editing for Discounts
* release DemoBundle for installing a Demo Store, and a better Frontend theme
* ensure the service architecture is good enough to accommodate all types of bundles
* finish documentation
* finish order creation, checkout events
* admin dashboard
* move choice filters in admin
* change repository search field to be an array instead of a single value

# From Beta to Release Candidate

* better code coverage in Unit Tests
* some PCI compliance
* better Twig functions for images, improve support for CDNs
* possibly move Route annotations into routing.yml
* possibly change services.xml to services.yaml
* possibly change Injection in services.xml to use constructors instead of setters, but probably keep setters available to other Bundles
* finish Single Page Application / javascript implementation
* At least 1 of the major Shipping vendors is integrated : Fedex, UPS, USPS
* Stripe and Authorize.net Payment Gateway bundles are released
* ensure the (currently un-released) ElasticSearch bundle works with the Frontend architecture

# From Release Candidate to 1.0

* PCI compliance is complete
* Unit Tests are as complete as possible
* potential bugs are fixed
