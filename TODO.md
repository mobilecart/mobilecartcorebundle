# From Alpha to Beta:

* better Mass Import and Export functions
* refactor Entity EAV update to support variant codes, finish how EAV insert does
* look at refactoring EAV form fields to use variant codes instead of var_X input names
* some Unit Tests
* finish Discount handling and Admin editing for Discounts
* release DemoBundle for installing a Demo Store, and a better Frontend theme
* ensure the service architecture is good enough to accommodate all types of bundles

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
