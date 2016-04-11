# Orders and Payments

In order to keep the subscription bundle an add-on,
 and avoid a tight coupling, some rules and assumptions were created:

Files involved:

* Services/OrderService.php
* Services/PaymentService.php
* Payment/PaymentMethodServiceInterface.php

How it works:

* PaymentService retrieves a PaymentMethodService , which implements PaymentMethodServiceInterface
* OrderService contains the PaymentService
* OrderService captures payments via a PaymentMethodService

Some assumptions:

* OrderService calls capturePayment() and doesn't care how the PaymentMethodService captures
* capturing a payment may require a subscription or token to be created
* OrderService is only concerned with whether a payment was captured
* post-processing of subscriptions, etc should happen in Event: order.submit.success

Using eventData in OrderService:

* Subscriptions commonly use the same products for creating a subscription as taking a subscription payment
* When observing Event: order.submit.success , listeners should look for flags being passed from eventData in OrderService
* For example, capturing a subscription payment and creating a subscription order should not create another subscription
